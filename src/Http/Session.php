<?php

namespace Microflex\Http;

use Microflex\Utils\Security;

class Session
{
    protected $security;
    protected $cookie;

    public function __construct(Security $security, Cookie $cookie)
    {
        $this->security = $security;
        $this->cookie = $cookie;
    }

    public static function getInstance()
    {
        $security = new Security;
        $cookie = new Cookie($security);
        return new Session($security, $cookie);
    }

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

        $_SESSION[$key] = [ $this->security->sanitize($value), $isFlashed ];
    }

    public function get($key)
    {
        $this->start();

        return $this->security->sanitize($_SESSION[$key][0] ?? null);
    }

    public function all()
    {
        $this->start();

        $sessionArr = [];

        foreach ($_SESSION as $key => $value) {

            $sessionArr[$key] = $value[0];
        }

        return $this->security->sanitize($sessionArr);
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
        $this->cookie->unset(session_name());

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