<?php

namespace Microflex\Http;

use Microflex\Utils\Security;

class Cookie
{
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function set($name, $value = "", $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        $this->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function unset($name, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        $this->setCookie($name, "", 1, $path, $domain, $secure, $httpOnly);
    }

    public function get($name)
    {
        return $this->security->sanitize($_COOKIE[$name] ?? null);
    }

    public function all()
    {
        return $this->security->sanitize($_COOKIE);    
    }

    protected function setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}