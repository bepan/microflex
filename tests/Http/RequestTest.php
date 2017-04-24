<?php

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function test_all_method_returns_dollar_GET_when_any_get_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET = ['name' => '<h1>Alberto</h1>', 'age' => 25];

        $request = $this->getMockBuilder(Microflex\Http\Request::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['getHeaders'])
                        ->getMock();

        $request->method('getHeaders')
                ->willReturn(['Content-Type' => 'application/json', 'User-Agent' => 'Mozilla']);

        $this->assertEquals(['name' => '&lt;h1&gt;Alberto&lt;/h1&gt;', 'age' => 25], $request->all());

        $this->assertEquals('&lt;h1&gt;Alberto&lt;/h1&gt;', $request->input('name'));
    }

    public function test_all_method_returns_dollar_AJAX_when_any_nonget_ajax_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = $this->getMockBuilder(Microflex\Http\Request::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['getHeaders', 'getPHPInput'])
                        ->getMock();

        $request->method('getHeaders')
                ->willReturn(['Content-Type' => 'application/json', 'User-Agent' => 'Mozilla']);

        $request->method('getPHPInput')
                ->willReturn(['name' => 'Marco', 'age' => 24]);

        $this->assertEquals(['name' => 'Marco', 'age' => 24], $request->all());

        $this->assertEquals('Marco', $request->input('name'));
    }

    public function test_all_method_returns_dollar_POST_when_form_data_post_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST = ['name' => 'Ada', 'age' => 22];

        $request = $this->getMockBuilder(Microflex\Http\Request::class) 
                        ->disableOriginalConstructor()
                        ->setMethods(['getHeaders'])
                        ->getMock();

        $this->assertEquals(['name' => 'Ada', 'age' => 22], $request->all());

        $this->assertEquals('Ada', $request->input('name'));
    }

    public function test_getHeader_method_returns_sanitized_header_value()
    {
        $request = $this->getMockBuilder(Microflex\Http\Request::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['getRawHeaders'])
                        ->getMock();

        $request->method('getRawHeaders')
                ->willReturn(['token' => '<12345', 'User-Agent' => 'Mozilla']);

        $this->assertEquals('&lt;12345', $request->header('token'));
    }

    public function test_getHeaders_method_returns_sanitized_header_array()
    {
        $request = $this->getMockBuilder(Microflex\Http\Request::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['getRawHeaders'])
                        ->getMock();

        $request->method('getRawHeaders')
                ->willReturn(['token' => '<12345', 'User-Agent' => 'Mozilla']);

        $this->assertEquals(['token' => '&lt;12345', 'User-Agent' => 'Mozilla'], $request->headers());
    }
}
