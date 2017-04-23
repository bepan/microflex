<?php

namespace Microflex\Http;

class Request
{
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

        return htmlspecialchars($_AJAX[$key] ?? $_POST[$key] ?? $_GET[$key] ?? null);
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