<?php

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function setUp()
    {
        $this->security = new Microflex\Utils\Security;
        $this->urlStub = $this->createMock(Microflex\Http\Url::class);
        $this->sessionStub = $this->createMock(Microflex\Http\Session::class);
        $this->cookieStub = $this->createMock(Microflex\Http\Cookie::class);

        $this->request = $this->getMockBuilder(Microflex\Http\Request::class)
                        ->setConstructorArgs([
                            $this->security, 
                            $this->urlStub, 
                            $this->sessionStub, 
                            $this->cookieStub
                        ])
                        ->setMethods(['getHeaders', 'getPHPInput', 'getRawHeaders'])
                        ->getMock();
    }

    public function test_all_method_returns_dollar_GET_when_any_get_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET = ['name' => 'get'];

        $this->request->method('getHeaders')
                      ->willReturn(['Content-Type' => 'application/json', 'User-Agent' => 'Mozilla']);

        $this->assertEquals(['name' => 'get'], $this->request->all());

        $this->assertEquals('get', $this->request->input('name'));
    }

    public function test_all_method_returns_dollar_AJAX_when_any_nonget_ajax_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $this->request->method('getHeaders')
                      ->willReturn(['Content-Type' => 'application/json', 'User-Agent' => 'Mozilla']);

        $this->request->method('getPHPInput')
                      ->willReturn(['name' => 'ajax']);

        $this->assertEquals(['name' => 'ajax'], $this->request->all());

        $this->assertEquals('ajax', $this->request->input('name'));
    }

    public function test_all_method_returns_dollar_POST_when_form_data_post_request()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST = ['name' => 'post'];

        $this->assertEquals(['name' => 'post'], $this->request->all());

        $this->assertEquals('post', $this->request->input('name'));
    }

    public function test_getHeader_method_returns_right_header()
    {
        $this->request->method('getRawHeaders')
                      ->willReturn(['token' => '12345', 'User-Agent' => 'Mozilla']);

        $this->assertEquals('12345', $this->request->getHeader('token'));
    }

    public function test_getHeaders_method_returns_sanitized_header_array()
    {
        $this->request = $this->getMockBuilder(Microflex\Http\Request::class)
                              ->setConstructorArgs([
                                  $this->security, 
                                  $this->urlStub, 
                                  $this->sessionStub, 
                                  $this->cookieStub
                              ])
                              ->setMethods(['getRawHeaders'])
                              ->getMock();

        $this->request->method('getRawHeaders')
                      ->willReturn(['token' => '12345', 'User-Agent' => 'Mozilla']);

        $this->assertEquals(['token' => '12345', 'User-Agent' => 'Mozilla'], $this->request->getHeaders());
    }
}
