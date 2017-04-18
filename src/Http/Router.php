<?php

namespace Microflex\Http;

class Router extends RouterBase
{
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

        list($uri, $ownMiddlewares, $callback) = $this->validateMethodArgs($args);
        
        $this->registerRoute($method, $uri, $ownMiddlewares, $callback);

        $this->lastCallbackTypeRegistered = 'method';
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

    public function handle404(...$callbacks)
    {
        if (count($callbacks) === 0) {

            throw new \Exception('You must provide at least 1 argument to the use method.');
        }

        foreach($callbacks as $callback) {

            $this->validateCallback($callback);    

            $this->globalMiddlewares[] = $this->parseCallback($callback);    

            $this->lastCallbackTypeRegistered = 'middleware';
        }
    }

    public function group($prefix, \Closure $closure)
    {
        if (!is_string($prefix)) {

            throw new \Exception('The prefix must be a string.');
        }

        $this->routePrefixes[] = $prefix;

        $closure();

        array_pop($this->routePrefixes);
    }
}
