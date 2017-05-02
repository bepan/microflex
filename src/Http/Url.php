<?php

namespace Microflex\Http;

use Microflex\Utils\Security;

class Url
{
    protected $params = [];

    protected $queries = [];

    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;

        if (isset($_SERVER['QUERY_STRING'])) {

            parse_str($_SERVER['QUERY_STRING'], $this->queries);
        }
    }

    public function setUrlParams(array $urlParams)
    {
        $explodeUri = explode('/', $_SERVER['REQUEST_URI']);
        
        foreach ($urlParams as $key => $value) {

            $this->params[$key] = preg_replace('/\?.*/', '', $explodeUri[$value]);
        }
    }

    public function query($name)
    {
        return $this->security->sanitize($this->queries[$name] ?? null);
    }

    public function queries()
    {
        return $this->security->sanitize($this->queries);
    }

    public function param($name)
    {
        return $this->security->sanitize($this->params[$name] ?? null);
    }

    public function params()
    {
        return $this->security->sanitize($this->params);
    }
}
