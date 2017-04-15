<?php

namespace Microflex\Http;

abstract class RouterBase
{
    protected static $URL_PARAM_PATTERN = '/:[a-zA-Z]+/';

    protected $routes = [];

    protected $globalMiddlewares = [];

    protected $globalMiddlewaresUsed = [];

    protected $lastCallbackTypeRegistered = 'method'; // to check if the last callback was middleware or http method.
    
    protected $cachedObjects = []; // to deal with singleton pattern
    
    protected $nextMiddleware = false;

    protected $routePrefix = '';

    protected $groupMiddlewares = [];


    protected function executeMiddlewares($middlewares, $urlParams)
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
            
            if (array_key_exists($classType, $this->cachedObjects)) {
                
                $finalParam = $this->cachedObjects[$classType];
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

                    $this->cachedObjects[$classType] = $finalObject;
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

        // attach global and own middlewares to callback.
        $this->globalMiddlewaresUsed = array_merge($this->globalMiddlewaresUsed, $this->globalMiddlewares);

        $this->globalMiddlewares = [];
        
        $middlewares = array_merge($this->globalMiddlewaresUsed, $groupMiddlewaresParsed, $ownMiddlewaresParsed);
        
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
}