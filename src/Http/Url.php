<?php

namespace Microflex\Http;

use Microflex\Utils\Security;
use Microflex\Utils\Url as UtilsUrl;

class Url
{
    protected $params = [];
    protected $queries = [];
    protected $security;
    protected $utilsUrl;

    public function __construct(Security $security, UtilsUrl $utilsUrl)
    {
        $this->utilsUrl = $utilsUrl;

        $this->security = $security;

        if (isset($_SERVER['QUERY_STRING'])) {

            parse_str($_SERVER['QUERY_STRING'], $this->queries);
        }
    }

    public function setUrlParams(array $urlParams)
    {
        $uri = $_SERVER['REQUEST_URI'];

        $uriWithlt = preg_replace('/%3C/', '<', $uri);

        $completeUri = preg_replace('/%3E/', '>', $uriWithlt);

        $explodeUri = $this->utilsUrl->splitUri($completeUri);
        
        // echo $_SERVER['REQUEST_URI'], '<br/>';
        // print_r($explodeUri);
        // exit();
        
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
