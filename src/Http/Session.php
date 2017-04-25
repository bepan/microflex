<?php

namespace Microflex\Http;

class Session
{
    public function set($key, $value)
    {
        session_start();

        $_SESSION[$key] = [ htmlspecialchars($value), false ];
    }

    public function get($key)
    {
        session_start();

        return htmlspecialchars($_SESSION[$key][0] ?? null);
    }

    public function all()
    {
        session_start();

        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $_SESSION); 
    }

    public function unset($key)
    {
        session_start();

        unset($_SESSION[$key]);
    }

    public function destroy()
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