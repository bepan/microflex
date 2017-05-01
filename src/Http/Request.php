<?php

namespace Microflex\Http;

use Microflex\Utils\Security;

class Request
{
    protected $security;
    protected $url;
    protected $session;
    protected $cookie;

    public function __construct(Security $security, Url $url, Session $session, Cookie $cookie)
    {
        $this->security = $security;
        $this->url = $url;
        $this->session = $session;
        $this->cookie = $cookie;
    }

    public function all()
    {
        $headers = $this->getHeaders();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            return $this->security->sanitize($_GET);
        }

        if ( isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json' ) {

            $_AJAX = $this->getPHPInput();

            return $this->security->sanitize($_AJAX);
        }

        return $this->security->sanitize($_POST);
    }

    public function input($key)
    {
        $_AJAX = $this->getPHPInput();

        return $this->security->sanitize($_AJAX[$key] ?? $_POST[$key] ?? $_GET[$key] ?? null);
    }

    public function getHeader($name)
    {
        $headers = $this->getRawHeaders();

        return $this->security->sanitize($headers[$name] ?? null);
    }

    public function getHeaders()
    {
        $headers = $this->getRawHeaders();

        return $this->security->sanitize($headers);
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
        return getallheaders();
    }

    protected function getPHPInput()
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}