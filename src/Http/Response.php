<?php

namespace Microflex\Http;

class Response
{
    public function __construct(Session $session, Request $request)
    {
        $this->session = $session;

        $this->request = $request;
    }

    public function redirect($url)
    {
        header("Location: {$url}");

        return $this;
    }

    public function with($key, $value)
    {
        $this->session->set($key, $value);
    }

    public function withInput()
    {
        $input = $this->request->all();

        $this->session->set('php_input_session', $input, true);
    }

    public function render($filePath, array $data = [])
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

    public function json(array $content, $code = 200)
    {
        $this->setCode($code);

        $this->setContentType('json');

        echo json_encode($content);
    }

    public function setCode($code)
    {
        $this->http_response_code($code);

        return $this;
    }

    public function setContentType($type)
    {
    	switch ($type) {
            
    		case 'plain':
    		case 'html':
                $this->set_header('Content-Type', "text/{$type}");
    			break;

    		case 'json':
    		case 'xml':
                $this->set_header('Content-Type', "application/{$type}");
    			break;
    		
    		default:
    			throw new \Exception("Content-Type: $type, not exists.");
    			break;
    	}

    	return $this;
    }

    protected function http_response_code($code)
    {
        http_response_code($code);
    }

    protected function set_header($header, $value)
    {
        header("$header: $value");
    }
}
