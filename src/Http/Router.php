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

    public function group(array $config, \Closure $closure)
    {
        $middlewareKey = 'middleware';

        if ( count($config) !== 1 || 
             (!array_key_exists('prefix', $config) && !array_key_exists($middlewareKey, $config)) ) {

            throw new \Exception('The criteria group array must contain only one key. (prefix or middleware)');
        }

        if (array_key_exists($middlewareKey, $config)) {
            
            if (is_array($config[$middlewareKey])) {

                $this->groupMiddlewares = array_merge($this->groupMiddlewares, $config[$middlewareKey]);
            }
            elseif (is_string($config[$middlewareKey])) {

                $this->groupMiddlewares[] = $config[$middlewareKey];
            }
            else {

                throw new \Exception('The middlewares key must be array or string.');
            }
        }
        else {

            if (!is_string($config['prefix'])) {    

                throw new \Exception('The prefix must be a string.');
            }

            $this->routePrefixes[] = $config['prefix'];
        }

        $closure();
        
        array_key_exists($middlewareKey, $config) ? array_pop($this->groupMiddlewares) : array_pop($this->routePrefixes);
    }
}
