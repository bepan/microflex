<?php

namespace Betopan\Http;

class Router extends RouterBase
{
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
                
                $this->executeMiddlewares($route['middlewares'], $route['urlParams']);
                return;
            }
        }

        if ($this->lastTypeRegistered === 'middleware') { // Execute trailing middlewares

            $this->executeMiddlewares($this->middlewares, $route['urlParams']);
            return;
        }
        
        http_response_code(404);

        echo 'Resource not found.';
    }

    private function executeMiddlewares($middlewares, $urlParams)
    {
        foreach ($middlewares as $callback) {

            $refFunc = is_array($callback) ? new \ReflectionMethod($callback[0], $callback[1]) : new \ReflectionFunction($callback);            

            $mainParams = $this->getCallbackParams($refFunc);     

            $this->populateReqObjectWithUrlParams($mainParams, $urlParams);    

            $cbResult = $this->executeMiddleware($callback, $mainParams);

            if ($cbResult === null) return; // break the callback stack if middlwares dont return th next cb.
        }
    }

    public function use(Callable $callable)
    {
        $this->middlewares[] = $callable;

        $this->lastTypeRegistered = 'middleware';
    }
}
