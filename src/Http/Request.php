<?php

namespace Microflex\Http;

class Request
{
    public function all()
    {
        $_AJAX = json_decode(file_get_contents('php://input'), true);
     
        $headers = $this->getHeaders();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            return array_map(function($value) {
                return htmlspecialchars($value);
            }, $_GET);
        }

        if ( isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json' ) {

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
        $_AJAX = json_decode(file_get_contents('php://input'), true);

        return htmlspecialchars($_GET[$key] ?? $_POST[$key] ?? $_AJAX[$key] ?? null);
    }

    public function getHeader($name)
    {
        $headers = getallheaders();

        return htmlspecialchars($headers[$name] ?? null);
    }

    public function getHeaders()
    {
        $headers = getallheaders();
        
        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $headers);
    }

    public function getCookie($name)
    {
        //
        return htmlspecialchars($_COOKIE[$name] ?? null);
    }

    public function getCookies()
    {
        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $_COOKIE);     
    }

    public function setSessionValue($key, $value)
    {
        session_start();

        $_SESSION[$key] = [ htmlspecialchars($value), false ];
    }

    public function getSessionValue($key)
    {
        session_start();

        return htmlspecialchars($_SESSION[$key][0] ?? null);
    }

    public function getSession()
    {
        session_start();

        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $_SESSION); 
    }

    public function unsetSessionValue($key)
    {
        session_start();

        unset($_SESSION[$key]);
    }

    public function destroySession()
    {
        session_start();

        //remove PHPSESSID from browser
        if ( isset($_COOKIE[session_name()]) ) {

            setcookie(session_name(), "", time() - 3600, "/");
        }

        //clear session from globals
        $_SESSION = [];

        //clear session from disk
        session_destroy();
    }
}