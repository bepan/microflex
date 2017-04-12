<?php

namespace Betopan\Http;

abstract class RouterBase
{
    protected static $URL_PARAM_PATTERN = '/:[a-zA-Z]+/';

    protected $routes = [];

    protected function executeRoute($callback, $mainParams)
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

    protected function populateReqObjectWithUrlParams(&$mainParams, $urlParams)
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

    protected function registerRoute($methodType, $path, $callback)
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

    protected function buildUrlRegex($path)
    {
        $pathReplaced = preg_replace(self::$URL_PARAM_PATTERN, '.+', $path); // replace param chunks
        
        // build url regex.
        $escapedPath = "{$this->escapeForRegex($pathReplaced)}(\/)?";

        $queryStringPattern = '(\?([a-z]+)?=?([^&]+)?(&([a-z]+)?=?([^&]+)?)*)?';
        
        return "/^{$escapedPath}{$queryStringPattern}$/";
    }

    protected function parseIfCallbackString($callback)
    {
        if (is_string($callback)) {

            if (preg_match('/^[a-zA-Z\\\\]+@[a-zA-Z0-9]+$/', $callback) === 0) {

                throw new \Exception('Invalid string callback format.');
            }
            
            return explode('@', $callback);
        }

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
}