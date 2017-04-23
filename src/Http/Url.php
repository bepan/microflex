<?php

namespace Microflex\Http;

class Url
{
    public function query($name)
    {
        if ( !isset($_SERVER['QUERY_STRING']) ) return null;

        parse_str($_SERVER['QUERY_STRING'], $_QUERIES);

        return htmlspecialchars( $_QUERIES[$name] ?? null );
    }

    public function queries()
    {

    }

    public function param()
    {

    }

    public function params()
    {

    }
}