<?php

namespace Microflex\Http;

class Router extends RouterBase
{
    public $methods = [
       'get',
       'post', 
       'delete',
       'put', 
       'patch'
    ];

    public function getRoutes()
    {
        // get original routes as a copy
        return $this->routes;
    }
    
    public function __call($method, $args)
    {
        if (!in_array($method, $this->methods)) {
           
            throw new \Exception("{$method} method does not exists in router class.");
        }
        
        $uri = "{$this->routePrefix}{$args[0]}";
        $ownMiddlewares = [];

        if (count($args) === 2) {

            $callback = $args[1];
        }
        else {

            $callback = $args[2];
            $ownMiddlewares = $args[1];
        }

        if (!is_string($uri)) {

            throw new \Exception('Uri must be a string.');
        }

        $this->validateCallback($callback); // validate callback
        
        $this->registerRoute($method, $uri, $ownMiddlewares, $callback);

        $this->lastCallbackTypeRegistered = 'method';
    }

    private function validateCallback($callback)
    {
        if ( !(is_object($callback) && $callback instanceof \Closure) && 
             !is_string($callback) ) {

            throw new \Exception('Callback must be a closure or a string');
        }
    }

    public function activate()
    {
        $currentUri = $_SERVER['REQUEST_URI'];

        $currentMethod = strtolower($_SERVER['REQUEST_METHOD']);

        if (!$this->uriExists($currentUri)) {

            if ($this->lastCallbackTypeRegistered === 'middleware') { // handle trailing middlewares    

                $this->executeMiddlewares($this->globalMiddlewares, []);    

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

    public function use(...$callbacks)
    {
        foreach($callbacks as $callback) {

            $this->validateCallback($callback);    

            $this->globalMiddlewares[] = $this->parseCallback($callback);    

            $this->lastCallbackTypeRegistered = 'middleware';
        }
    }

    public function group(array $config, \Closure $closure)
    {
        $this->routePrefix = $config['prefix'];

        $this->groupMiddlewares = $config['middlewares'];

        $closure();

        $this->routePrefix = '';

        $this->groupMiddlewares = [];
    }
}
