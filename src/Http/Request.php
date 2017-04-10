<?php
namespace Betopan\Http;

class Request
{
    public function input($key)
    {
    	$input = $_GET[$key] ?? $_POST[$key] ?? null;
    	
        return htmlspecialchars($input);
    }
}