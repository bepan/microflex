<?php

namespace Microflex;

use Microflex\Http\{Router, Cookie, Session};
use Microflex\Utils\Security;

class App
{
	protected $router;

    public function __construct()
    {
        require __DIR__ . '/Utils/ViewHelpers.php';
        $this->router = new Router;
        $this->setInputSession();
        $this->setSession();
    }

    protected function setInputSession()
    {
        $session = Session::getInstance();

        $session->start();

        $inputSession = $session->get('php_input_session');

        if ( $inputSession !== null ) {

            $GLOBALS['php_input_session'] = $inputSession;

            unset($_SESSION['php_input_session']);

            return;
        }
        
        $GLOBALS['php_input_session'] = [];
    }

    protected function setSession()
    {
        $session = Session::getInstance();

        $session->start();

        $GLOBALS['php_flashed_session'] = [];

        foreach ($_SESSION as $key => $value) {
            
            if ($value[1]) {

                $GLOBALS['php_flashed_session'][$key] = $value[0];

                unset($_SESSION[$key]);
            }
        }
    }

    public function get($uri, $callback)
    {
        $this->router->get($uri, $callback);
    }

    public function post($uri, $callback)
    {
        $this->router->post($uri, $callback);
    }

    public function put($uri, $callback)
    {
        $this->router->put($uri, $callback);
    }

    public function patch($uri, $callback)
    {
        $this->router->patch($uri, $callback);
    }

    public function delete($uri, $callback)
    {
        $this->router->delete($uri, $callback);
    }

    public function handle404(...$callbacks)
    {
        $this->router->handle404(...$callbacks);
    }

    public function group(array $config, \Closure $closure)
    {
        $this->router->group($config, $closure);
    }

    public function activate()
    {
        $this->router->activate();
    }
}