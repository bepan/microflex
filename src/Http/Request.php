<?php

namespace Microflex\Http;

class Request
{
    protected $url;
    protected $session;
    protected $cookie;

    public function __construct(array $urlParams)
    {
        $this->url = new Url($urlParams);

        $this->session = new Session;

        $this->cookie = new Cookie;
    }

    public function all()
    {
        $headers = $this->getHeaders();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            return array_map(function($value) {

                return htmlspecialchars($value);

            }, $_GET);
        }

        if ( isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json' ) {

            $_AJAX = $this->getPHPInput();

            return array_map(function($value) {

                return htmlspecialchars($value);

            }, $_AJAX);
        }

        return array_map(function($value) {

            return htmlspecialchars($value);

        }, $_POST);
    }

    public function input($key)
    {
        $_AJAX = $this->getPHPInput();

        $value = $_AJAX[$key] ?? $_POST[$key] ?? $_GET[$key] ?? null;

        if ($value === null) return null;

        return htmlspecialchars($value);
    }

    public function header($name)
    {
        $headers = $this->getRawHeaders();

        return htmlspecialchars($headers[$name] ?? null);
    }

    public function headers()
    {
        $headers = $this->getRawHeaders();
        
        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $headers);
    }

    public function url()
    {
        return $this->url;
    }

    public function session()
    {
        return $this->session;
    }

    public function cookie()
    {
        return $this->cookie;
    }

    protected function getRawHeaders()
    {
        //
        return getallheaders();
    }

    protected function getPHPInput()
    {
        //
        return json_decode(file_get_contents('php://input'), true);
    }
}