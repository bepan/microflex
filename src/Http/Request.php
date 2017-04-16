<?php

namespace Microflex\Http;

class Request
{
    public function input($key)
    {	
        return htmlspecialchars($_GET[$key] ?? $_POST[$key] ?? null);
    }

    public function getCookie($name = null)
    {
        if ($name === null) {
            
            return array_map(function($value) {
                
                return htmlspecialchars($value);

            }, $_COOKIE);
        }

        return htmlspecialchars($_COOKIE[$name] ?? null);
    }

    public function setSession($key, $value)
    {
        session_start();

        $_SESSION[$key] = htmlspecialchars($value);
    }

    public function getSession($key)
    {
        session_start();

        return htmlspecialchars($_SESSION[$key] ?? null);
    }

    public function unsetSession($key)
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