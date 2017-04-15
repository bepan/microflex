<?php

namespace Microflex\Http;

class Response
{
    public function redirect($url)
    {
        header("Location: {$url}");
    }

    public function render($filePath, array $data)
    {
        $this->setContentType('html');
        
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }

        require "{$filePath}.php";
    }

    public function send($content, $code = 200)
    {
    	$this->setCode($code);

        echo $content;
    }

    public function json(array $content)
    {
        $this->setContentType('json');

        echo json_encode($content);
    }

    public function setCode($code)
    {
        http_response_code($code);

        return $this;
    }

    public function setContentType($type)
    {
    	switch ($type) {
            
    		case 'plain':
    		case 'html':
    		    header("Content-Type: text/{$type}");
    			break;

    		case 'json':
    		case 'xml':
    		    header("Content-Type: application/{$type}");
    			break;
    		
    		default:
    			header("Content-Type: text/plain");
    			break;
    	}

    	return $this;
    }
}
