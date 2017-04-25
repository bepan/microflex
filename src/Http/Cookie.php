<?php

namespace Microflex\Http;

class Cookie
{
    public function set($name, $value = "", $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function unset($name, $path = '/')
    {
        setcookie($name, "", time() - 3600, $path);
    }

    public function get($name)
    {
        //
        return htmlspecialchars($_COOKIE[$name] ?? null);
    }

    public function all()
    {
        return array_map(function($value) {
            
            return htmlspecialchars($value);

        }, $_COOKIE);     
    }
}