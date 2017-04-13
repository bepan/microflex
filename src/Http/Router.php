<?php

namespace Betopan\Http;

class Router extends RouterBase
{
    protected $methods = ['get', 'post', 'delete', 'put', 'patch'];

    public function __call($methodName, $args)
    {
       if (!in_array($methodName, $this->methods)) {
           
           throw new \Exception("{$methodName} router method, is not supported.");
       }

       $this->registerRoute($methodName, ...$args);
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
