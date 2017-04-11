<?php

namespace Betopan\Http;

class Router
{
    private static $URL_PARAM_PATTERN = '/:[a-zA-Z]+/';

    protected $routes = [];
    
    public function get($path, $callback)
    {
        $this->registerRoute('get', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->registerRoute('post', $path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->registerRoute('delete', $path, $callback);
    }

    public function put($path, $callback)
    {
        $this->registerRoute('put', $path, $callback);
    }

    public function patch($path, $callback)
    {
        $this->registerRoute('patch', $path, $callback);
    }

    public function activate()
    {
        foreach ($this->routes as $route) {

            if (preg_match($route['pattern'], $_SERVER['REQUEST_URI']) === 1) {

            	if (strtolower($_SERVER['REQUEST_METHOD']) !== $route['method']) { // check correct request method.

                    http_response_code(405);

                    echo "{$method} method not allowed";

                    return;
            	}

                $this->executeRoute($route['callback'], $route['urlParams']);

                return;
            }
        }
        
        http_response_code(404);

        echo 'Resource not found.';
    }

    private function executeRoute($callback, $urlParams)
    {
        $refFunc = is_array($callback) ? new \ReflectionMethod($callback[0], $callback[1]) : new \ReflectionFunction($callback);

        $mainParams = $this->getCallbackParams($refFunc);

        $this->populateReqObjectWithUrlParams($mainParams, $urlParams);

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

    private function populateReqObjectWithUrlParams(&$mainParams, $urlParams)
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

    private function getCallbackParams($reflectionFunc)
    {
        if (count($reflectionFunc->getParameters()) === 0) {

            return [];
        }
        
        foreach ($reflectionFunc->getParameters() as $param) {

            preg_match('/\[.*\]/', $param, $output);

            $splitParam = explode(' ', $output[0]);

            $subfinalParams = [];

            if (method_exists($splitParam[2], '__construct')) {

                $subrefFunc = new \ReflectionMethod($splitParam[2], '__construct');

                $subfinalParams = $this->getCallbackParams($subrefFunc);
            }

            $finalParam = new $splitParam[2]( ...$subfinalParams );

            $finalParams[] = $finalParam;
        }

        return $finalParams;
    }

    private function registerRoute($methodType, $path, $callback)
    {
        if (!is_callable($callback) && !is_string($callback)) {

            throw new \Exception('Invalid callback type passed to the router.');
        }
        
        $callback = $this->parseIfCallbackString($callback); // parse if callback is a class method.
        
        $urlParams = $this->getUrlParamNames($path);
        
        $fullUrlRegex = $this->buildUrlRegex($path);
        
        $this->routes[] = [ // saving the route in route array.
            'method'   => $methodType,
            'pattern'  => $fullUrlRegex,
            'callback' => $callback,
            'urlParams' => $urlParams
        ];
    }

    private function buildUrlRegex($path)
    {
        $pathReplaced = preg_replace(self::$URL_PARAM_PATTERN, '[a-z0-9]+', $path); // replace param chunks
        
        // build url regex.
        $escapedPath = "{$this->escapeForRegex($pathReplaced)}(\/)?";

        $queryStringPattern = '(\?([a-z]+)?=?([^&]+)?(&([a-z]+)?=?([^&]+)?)*)?';
        
        return "/^{$escapedPath}{$queryStringPattern}$/";
    }

    private function parseIfCallbackString($callback)
    {
        if (is_string($callback)) {

            if (preg_match('/^[a-zA-Z\\\\]+@[a-zA-Z0-9]+$/', $callback) === 0) {

                throw new \Exception('Invalid string callback format.');
            }
            
            return explode('@', $callback);
        }

        return $callback;
    }

    private function getUrlParamNames($path)
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

    private function escapeForRegex($source)
    {
        return preg_replace('/\//', '\/', $source);
    }
}
