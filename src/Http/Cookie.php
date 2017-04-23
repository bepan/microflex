<?php

namespace Microflex\Http;

class Cookie
{
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
}