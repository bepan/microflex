<?php

namespace Betopan\Http;

class Router
{
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

                // check if its the correct request method.
            	if (strtolower($_SERVER['REQUEST_METHOD']) !== $route['method']) {

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
        if (is_array($callback)) {

            $refFunc = new \ReflectionMethod($callback[0], $callback[1]);
        }
        else {

            $refFunc = new \ReflectionFunction($callback);   
        }

        $mainParams = $this->getCallbackParams($refFunc, $urlParams);

        if (is_array($callback)) {

            $classDependencies = [];

            if (method_exists($callback[0], '__construct')) {

                $classRefFunc = new \ReflectionMethod($callback[0], '__construct');

                $classDependencies = $this->getCallbackParams($classRefFunc, $urlParams);
            }

            $object = new $callback[0](...$classDependencies);

            $object->{$callback[1]}(...$mainParams);

            return;
        }

        $callback(...$mainParams);
    }

    private function getCallbackParams($reflectionFunc, array $urlParams)
    {
        if (count($reflectionFunc->getParameters()) === 0) {

            return [];
        }
        
        foreach ($reflectionFunc->getParameters() as $param) {

            preg_match('/\[.*\]/', $param, $output);

            $splitParam = explode(' ', $output[0]);

            if (count($splitParam) === 4) {

                $argument = strtolower($splitParam[2]);

                if (!array_key_exists($argument, $urlParams)) {

                    throw new \Exception("Unknown callback argument: {$argument}");
                }

                $explodeUri = explode('/', $_SERVER['REQUEST_URI']);

                $finalParam = preg_replace('/\?.*/','', $explodeUri[$urlParams[$argument]]);
            } 
            else {

                $subfinalParams = [];

                if (method_exists($splitParam[2], '__construct')) {

                    $subrefFunc = new \ReflectionMethod($splitParam[2], '__construct');

                    $subfinalParams = $this->getCallbackParams($subrefFunc, $urlParams);
                }

                $finalParam = new $splitParam[2]( ...$subfinalParams );
            }

            $finalParams[] = $finalParam;
        }

        return $finalParams;
    }

    private function registerRoute($methodType, $path, $callback)
    {
        if (!is_callable($callback) && !is_string($callback)) {

            throw new \Exception('Invalid callback type passed to the router.');
        }
        
        $callback = $this->parseIfCallbackString($callback);
        
        // Url params stuff.
        $urlParamPattern = '/:[a-zA-Z]+/';
        
        $urlParams = $this->getUrlParamNames($path, $urlParamPattern);

        $path = preg_replace($urlParamPattern, '[a-z0-9]+', $path); // replace param chunks
        
        // build url regex.
    	$escapedPath = "{$this->escapeForRegex($path)}(\/)?";

        $queryStringPattern = '(\?([a-z]+)?=?([^&]+)?(&([a-z]+)?=?([^&]+)?)*)?';
        
        $regexPattern = "/^{$escapedPath}{$queryStringPattern}$/";
        
        // saving the route in route array.
        $this->routes[] = [
            'method'   => $methodType,
            'pattern'  => $regexPattern,
            'callback' => $callback,
            'urlParams' => $urlParams
        ];
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

    private function getUrlParamNames($path, $pattern)
    {
        $urlParams = [];

        $explodePath = explode('/', $path);

        preg_match_all($pattern, $path, $outputParams);

        foreach ($outputParams[0] as $param) {

            $key = array_search($param, $explodePath);

            $value = strtolower(str_replace(':', '$', $param));

            $urlParams[$value] = $key;
        }

        return $urlParams;
    }

    private function escapeForRegex($source)
    {
        return preg_replace('/\//', '\/', $source);
    }
}
