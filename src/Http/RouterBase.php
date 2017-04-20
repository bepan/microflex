<?php

namespace Microflex\Http;

abstract class RouterBase
{
    protected $methods = [
       'get',
       'post', 
       'delete',
       'put', 
       'patch'
    ];

    protected static $URL_PARAM_PATTERN = '/:[a-zA-Z]+/';

    protected $routes = [];
    
    protected $cachedArguments = []; // to deal with singleton pattern
    
    protected $nextMiddleware = false;

    protected $routePrefixes = [];

    protected $groupMiddlewares = [];

    protected $notFoundMiddlewares = [];
    
    protected function setInputSession()
    {
        // Set input session from forms.
        session_start();

        global $php_input_session;

        if ( isset($_SESSION['php_input_session']) ) {

            $php_input_session = $_SESSION['php_input_session'];

            unset($_SESSION['php_input_session']);

            return;
        }
        
        $php_input_session = [];
    }

    protected function executeMiddlewares($middlewares, $urlParams = [])
    {
        foreach ($middlewares as $callback) {

            if (is_array($callback)) {

                $refFunc = new \ReflectionMethod($callback[0], $callback[1]);

                if ($refFunc->isStatic()) {

                    throw new \Exception('cannot use static methods for controllers.');
                }
            }
            else {
                
                $refFunc = new \ReflectionFunction($callback);
            }

            $mainParams = $this->getCallbackParams($refFunc);  

            $this->populateReqObjectWithUrlParams($mainParams, $urlParams);    

            $this->executeMiddleware($callback, $mainParams);

            if (!$this->nextMiddleware) return; // break the callback stack if middlwares dont execute the next cb.
            
            $this->nextMiddleware = false;
        }
    }

    protected function executeMiddleware($callback, $mainParams)
    {
        if (is_array($callback)) {

            $classDependencies = [];

            if (method_exists($callback[0], '__construct')) {

                $classRefFunc = new \ReflectionMethod($callback[0], '__construct');

                $classDependencies = $this->getCallbackParams($classRefFunc);
            }
        
            $object = new $callback[0](...$classDependencies);

            $object->{$callback[1]}(...$mainParams);

            return;
        }

        $callback(...$mainParams);
    }

    protected function populateReqObjectWithUrlParams($mainParams, $urlParams)
    {
        $reqObject = null;

        foreach ($mainParams as $param) {

            if ($param instanceof Request) {

                $reqObject = $param;
            }
        }

        if (count($urlParams) && $reqObject !== null) { // fill dynamic params array of req object.

            $explodeUri = explode('/', $_SERVER['REQUEST_URI']);
            
            foreach ($urlParams as $key => $value) {

                $reqObject->params[$key] = preg_replace('/\?.*/', '', $explodeUri[$value]);
            }
        }
    }

    protected function getCallbackParams($reflectionFunc)
    {
        if (count($reflectionFunc->getParameters()) === 0) {

            return [];
        }
        
        foreach ($reflectionFunc->getParameters() as $param) {

            preg_match('/\[.*\]/', $param, $output);

            $splitParam = explode(' ', $output[0]);

            if (count($splitParam) !== 5) {

                throw new \Exception('Arguments provided must be type hinted.');
            }

            $classType = $splitParam[2];
            
            if (array_key_exists($classType, $this->cachedArguments)) {
                
                $finalParam = $this->cachedArguments[$classType];
            }
            else {

                if ($classType === 'callable' || $classType === 'Closure') {
                    
                    $finalParam = function() { $this->nextMiddleware = true; };
                }
                else {    

                    $subfinalParams = [];        

                    if (method_exists($classType, '__construct')) {        

                        $subrefFunc = new \ReflectionMethod($classType, '__construct');       

                        $subfinalParams = $this->getCallbackParams($subrefFunc);
                    }

                    $finalObject = new $classType(...$subfinalParams);

                    $finalParam = $finalObject;

                    $this->cachedArguments[$classType] = $finalObject;
                }
            }

            $finalParams[] = $finalParam;
        }

        return $finalParams;
    }

    protected function registerRoute($method, $path, $ownMiddlewares, $callback)
    {
        $callback = $this->parseCallback($callback);

        $ownMiddlewaresParsed = array_map(function($value) {

            return $this->parseCallback($value);

        }, $ownMiddlewares);

        $groupMiddlewaresParsed = array_map(function($value) {

            return $this->parseCallback($value);
            
        }, $this->groupMiddlewares);
        
        $urlParams = $this->getUrlParamNames($path);
        
        $fullUrlRegex = $this->buildUrlRegex($path);

        // attach group and own middlewares to callback.
        $middlewares = array_merge($groupMiddlewaresParsed, $ownMiddlewaresParsed);
        
        $middlewares[] = $callback;
        
        $this->routes[] = [ // saving the route in route array.
            'method'      => $method,
            'pattern'     => $fullUrlRegex,
            'middlewares' => $middlewares,
            'urlParams'   => $urlParams
        ];
    }

    protected function buildUrlRegex($path)
    {
        $pathReplaced = preg_replace(self::$URL_PARAM_PATTERN, '.+', $path); // replace param chunks
        
        // build url regex.
        $escapedPath = "{$this->escapeForRegex($pathReplaced)}(\/)?";

        $queryStringPattern = '(\?([a-z]+)?=?([^&]+)?(&([a-z]+)?=?([^&]+)?)*)?';
        
        return "/^{$escapedPath}{$queryStringPattern}$/";
    }

    protected function parseCallback($callback)
    {        
        if (is_string($callback)) {

            if (preg_match('/^[^@]+(@[^@]+)?$/', $callback) === 0) {

                throw new \Exception('Invalid class method string format.');
            }

            $cbSplited = explode('@', $callback);

            if (count($cbSplited) === 1) {

                $cbSplited[] = 'run'; // default run method
            }

            return $cbSplited;
        }
        
        // do nothing if regular closure
        return $callback;
    }

    protected function getUrlParamNames($path)
    {
        $urlParams = [];

        $explodePath = explode('/', $path);

        preg_match_all(self::$URL_PARAM_PATTERN, $path, $outputParams);

        foreach ($outputParams[0] as $param) {

            $key = array_search($param, $explodePath);

            $value = strtolower(str_replace(':', '', $param));

            $urlParams[$value] = $key;
        }

        return $urlParams;
    }

    protected function escapeForRegex($source)
    {
        // regex path escape
        return preg_replace('/\//', '\/', $source);
    }

    protected function uriExists($uri)
    {
        foreach ($this->routes as $route) {
            
            if (preg_match($route['pattern'], $uri) === 1) {
                
                return true;
            }
        }

        return false;
    }

    protected function searchForUriAndMethod($uri, $method)
    {
        foreach ($this->routes as $route) {
            
            if (preg_match($route['pattern'], $uri) === 1 && $route['method'] === $method) {
                
                return $route;
            }
        }

        return null;
    }

    protected function validateCallback($callback)
    {
        if ( !(is_object($callback) && $callback instanceof \Closure) && 
             !is_string($callback) ) {

            throw new \Exception('Callback must be a closure or a string');
        }
    }

    protected function validateMethodArgs($args)
    {
        if (count($args) !== 2 && count($args) !== 3) {
 
            throw new \Exception('Register a method expects 2 or 3 arguments.');
        }
        
        // validate uri
        $prefixes = implode('', $this->routePrefixes);

        $ownMiddlewares = [];
        
        // validate uri
        $uri = $args[0];

        if (!is_string($uri)) {

            throw new \Exception('Uri must be a string.');
        }

        if (count($args) === 2) {

            $callback = $args[1];
        }
        else {

            if (!is_array($args[1])) {

                throw new \Exception('If register a method with 3 args, the second must be an array of middlewares.');
            }

            $callback = $args[2];

            $ownMiddlewares = $args[1];
        }

        $this->validateCallback($callback); // validate callback

        return [ "{$prefixes}{$uri}", $ownMiddlewares, $callback ];
    }

    protected function handleUriExistance($currentUri)
    {
        if (!$this->uriExists($currentUri)) {

            if (count($this->notFoundMiddlewares) > 0) { // handle trailing middlewares    

                $this->executeMiddlewares($this->notFoundMiddlewares);    

                exit();
            }
            
            http_response_code(404);    

            echo 'Resource not found.';

            exit();
        }
    }

    protected function validateGroupConfig(array $config, $middlewareKey)
    {
        if ( count($config) !== 1 || 
             (!array_key_exists('prefix', $config) && !array_key_exists($middlewareKey, $config)) ) {

            throw new \Exception('The criteria group array must contain only one key. (prefix or middleware)');
        }

        if (array_key_exists($middlewareKey, $config)) {
            
            if ( !is_array($config[$middlewareKey]) && !is_string($config[$middlewareKey]) ) {

                throw new \Exception('The middlewares key must be an array or string.');
            }
        }
        else {

            if (!is_string($config['prefix'])) {        

                throw new \Exception('The prefix must be a string.');
            }
        }
    }
}
