<?php

namespace Microflex;

use Microflex\Http\{Router, Cookie};
use Microflex\Utils\Security;

class App
{
	protected $router;

    public function __construct()
    {
        $this->router = new Router;
        $this->setInputSession();
    }

    protected function setInputSession()
    {
    	$security = new Security;
    	$cookie = new Cookie($security);
        $session = new Security($security, $cookie);

        $session->start();

        $inputSession = $session->get('php_input_session');

        if ( $inputSession !== null ) {

            $GLOBALS['php_input_session'] = $inputSession;

            unset($_SESSION['php_input_session']);

            return;
        }
        
        $GLOBALS['php_input_session'] = [];
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
}