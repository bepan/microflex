<?php

namespace Microflex\Http;

class Router extends RouterBase
{
    protected $methods = [
       'get',
       'post', 
       'delete',
       'put', 
       'patch'
    ];

    public function getRoutes()
    {
        return $this->routes;
    }
    
    public function __call($method, $args)
    {
        $methodType = $method;
        
        list($uri, $callback) = $args;

        if (!in_array($methodType, $this->methods)) {
           
            throw new \Exception("{$methodType} method does not exists in router class.");
        }

        if (count($args) !== 2) {

            throw new \Exception('You must provide exactly 2 arguments to register an http method.');
        }

        if ( !is_string($uri) ||
             (!(is_object($callback) && $callback instanceof \Closure) && !is_string($callback)) ) {

            throw new \Exception('Invalid argument types for method registration.');
        }

       $this->registerRoute($methodType, $uri, $callback);

       $this->lastCallbackTypeRegistered = 'method';
    }

    public function activate()
    {
        $currentUri = $_SERVER['REQUEST_URI'];

        $currentMethod = strtolower($_SERVER['REQUEST_METHOD']);

        if (!$this->uriExists($currentUri)) {

            if ($this->lastCallbackTypeRegistered === 'middleware') { // handle trailing middlewares    

                $this->executeMiddlewares($this->middlewares, []);    

                return;
            }
            
            http_response_code(404);    

            echo 'Resource not found.';

            return;
        }

        $route = $this->searchForUriAndMethod($currentUri, $currentMethod);
        
        if ($route === null) {

            http_response_code(405);

            echo "{$currentMethod} method not supported for {$currentUri}";

            return;
        }
                
        $this->executeMiddlewares($route['middlewares'], $route['urlParams']);
    }

    public function use(Callable $callable)
    {
        $this->middlewares[] = $callable;

        $this->lastCallbackTypeRegistered = 'middleware';
    }
}
