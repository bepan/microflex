<?php

namespace Microflex\Http;

class Session
{
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {

            session_start();
        }
    }

    public function set($key, $value)
    {
        $this->start();

        $_SESSION[$key] = [ htmlspecialchars($value), false ];
    }

    public function get($key)
    {
        $this->start();

        return htmlspecialchars($_SESSION[$key][0] ?? null);
    }

    public function all()
    {
        $this->start();

        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $_SESSION); 
    }

    public function unset($key)
    {
        $this->start();

        unset($_SESSION[$key]);
    }

    public function destroy()
    {
        $this->start();

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