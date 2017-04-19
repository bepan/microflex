<?php

namespace Microflex\Http;

class Router extends RouterBase
{
    public function __construct()
    {
        // Set input session from forms.
        session_start();

        global $php_input_session;

        if (isset($_SESSION['php_input_session'])) {

            $php_input_session = $_SESSION['php_input_session'];

            unset($_SESSION['php_input_session']);

            return;
        }
        
        $php_input_session = [];
    }

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
    }

    public function activate()
    {
        $currentUri = $_SERVER['REQUEST_URI'];

        $currentMethod = strtolower($_SERVER['REQUEST_METHOD']);

        // uri existance
        $this->handleUriExistance($currentUri);

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

            $this->notFoundMiddlewares[] = $this->parseCallback($callback);
        }
    }

    public function group(array $config, \Closure $closure)
    {
        $middlewareKey = 'middleware';

        $this->validateGroupConfig($config, $middlewareKey);

        if (array_key_exists($middlewareKey, $config)) {
            
            if (is_array($config[$middlewareKey])) {

                $this->groupMiddlewares = array_merge($this->groupMiddlewares, $config[$middlewareKey]);
            }
            else {

                $this->groupMiddlewares[] = $config[$middlewareKey];
            }
        }
        else {

            $this->routePrefixes[] = $config['prefix'];
        }

        $closure();
        
        array_key_exists($middlewareKey, $config) ? array_pop($this->groupMiddlewares) : array_pop($this->routePrefixes);
    }
    
}
