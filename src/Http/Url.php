<?php

namespace Microflex\Http;

class Url
{
    protected $params = [];

    protected $queries = [];

    public function __construct(array $urlParams)
    {
        $explodeUri = explode('/', $_SERVER['REQUEST_URI']);
        
        foreach ($urlParams as $key => $value) {

            $this->params[$key] = preg_replace('/\?.*/', '', $explodeUri[$value]);
        }

        if (isset($_SERVER['QUERY_STRING'])) {

            parse_str($_SERVER['QUERY_STRING'], $this->queries);
        }
    }

    public function query($name)
    {
        return htmlspecialchars( $this->queries[$name] ?? null );
    }

    public function queries()
    {
        return array_map(function($value) {
  
            return htmlspecialchars($value);

        }, $this->queries);
    }

    public function param($name)
    {
        return htmlspecialchars($this->params[$name] ?? null);
    }

    public function params()
    {
        return array_map(function($value) {
  
            return htmlspecialchars($value);

        }, $this->params);
    }
}