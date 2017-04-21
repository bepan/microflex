<?php

namespace Microflex\Http;

class Router extends RouterBase
{
    public function __construct()
    {
        // init stuff
        require_once __DIR__ . '/../helpers.php';
        $this->setInputSession();
    }
    
    public function __call($method, $args)
    {
        if (!in_array($method, $this->methods)) {
           
            throw new \Exception("{$method} method does not exists in router class.");
        }

        list($uri, $ownMiddlewares, $callback) = $this->validateMethodArgs($args);

        $prefixes = implode('', $this->routePrefixes);
        
        $this->registerRoute($method, "{$prefixes}{$uri}", $ownMiddlewares, $callback);
    }

    public function activate()
    {
        $currentUri = $_SERVER['REQUEST_URI'];

        $currentMethod = strtolower($_SERVER['REQUEST_METHOD']);

        // uri existance
        if(!$this->handleUriExistance($currentUri)) return;

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
        $numberOfMiddlewaresToRemove = 1;

        $this->validateGroupConfig($config, $middlewareKey);

        if (array_key_exists($middlewareKey, $config)) {
            
            if (is_array($config[$middlewareKey])) {

                $this->groupMiddlewares = array_merge($this->groupMiddlewares, $config[$middlewareKey]);
                
                $numberOfMiddlewaresToRemove = count($config[$middlewareKey]);
            }
            else {

                $this->groupMiddlewares[] = $config[$middlewareKey];
            }
        }
        else {

            $this->routePrefixes[] = $config['prefix'];
        }

        $closure();
        
        if(array_key_exists($middlewareKey, $config)) {

            for ($i=0; $i < $numberOfMiddlewaresToRemove; $i++) {

                array_pop($this->groupMiddlewares);
            }
        }
        else {

           array_pop($this->routePrefixes);
        }
    }
}