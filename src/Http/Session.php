<?php

namespace Microflex\Http;

class Session
{
    public function start()
    {
        if ($this->session_status() === PHP_SESSION_NONE) {

            $this->session_start();
        }
    }

    protected function session_status()
    {
        return session_status();
    }

    protected function session_start()
    {
        session_start();
    }

    public function set($key, $value, $isFlashed = false)
    {
        $this->start();

        $_SESSION[$key] = [ $this->sanitize($value), $isFlashed ];
    }

    protected function sanitize($value)
    {
        $new = [];

        if ( is_array($value) ) {

            foreach ($value as $v) {

                if ( is_array($v) ) {

                    $new[] = $this->sanitize($v);
                }
                else {
                
                    $new[] = htmlspecialchars($v);
                }
            }

            return $new;
        }
        
        return htmlspecialchars($value);
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
            
            return htmlspecialchars($value[0]);

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
        //$this->setcookie(session_name(), "", 1, "/");

        //clear session from globals
        $_SESSION = [];

        //clear session from disk
        $this->session_destroy();
    }

    protected function session_destroy()
    {
        session_destroy();
    }
}