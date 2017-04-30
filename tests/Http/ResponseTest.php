<?php

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function setUp()
    {
        $this->sessionStub = $this->createMock(Microflex\Http\Session::class);

        $this->requestStub = $this->createMock(Microflex\Http\Request::class);

        $this->response = $this->getMockBuilder(Microflex\Http\Response::class)
                               ->setConstructorArgs([$this->sessionStub, $this->requestStub])
                               ->setMethods(['set_header'])
                               ->getMock();
    }

    public function test_setContentType_with_html_calls_set_header_method_with_correct_args()
    {
        $this->response->expects($this->once())
                       ->method('set_header')
                       ->with('Content-Type', 'text/html');

        $this->response->setContentType('html');
    }

    public function test_setContentType_with_json_calls_set_header_method_with_correct_args()
    {
        $this->response->expects($this->once())
                 ->method('set_header')
                 ->with('Content-Type', 'application/json');

        $this->response->setContentType('json');
    }

    public function test_setContentType_with_unknown_type_throws_exception()
    {
        $this->expectException(Exception::class);

        $this->response->setContentType('foo');
    }

    public function test_setCode_with_200_calls_http_res_code_method_with_correct_arg()
    {
        $this->response = $this->getMockBuilder(Microflex\Http\Response::class)
                                ->disableOriginalConstructor()
                                ->setMethods(['http_response_code'])
                                ->getMock();

        $this->response->expects($this->once())
                       ->method('http_response_code')
                       ->with(200);

        $this->response->setCode(200);
    }

    public function test_json_method_with_assoc_array_calls_right_methods_and_echo_out_correct_json_string()
    {
        $this->expectOutputString('{"name":"beto","age":24}');

        $this->response = $this->getMockBuilder(Microflex\Http\Response::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['setCode', 'setContentType'])
                               ->getMock();

        $this->response->expects($this->once())
                 ->method('setCode')
                 ->with(201);

        $this->response->expects($this->once())
                 ->method('setContentType')
                 ->with('json');

        $this->response->json([
            'name' => 'beto',
            'age'  => 24
        ], 201);
    }

    public function test_send_method_with_content_sets_right_code_and_outputs_right_content()
    {
        $this->expectOutputString('<h1>hello world</h1>');

        $this->response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['setCode'])
                         ->getMock();

        $this->response->expects($this->once())
                 ->method('setCode')
                 ->with(200);

        $this->response->send('<h1>hello world</h1>');
    }

    public function test_render_method_renders_right_content()
    {
        $this->expectOutputString('<p>beto</p>');

        $this->response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['setContentType'])
                         ->getMock();

        $this->response->render(__DIR__ . '/../foo', ['var' => 'beto']);
    }

    public function test_redirect_method()
    {
        $this->response->expects($this->once())
                       ->method('set_header')
                       ->with('Location', '/home');

        $this->response->redirect('/home');    
    }

    public function test_with_method_sets_right_session_values()
    {
        $this->sessionStub->expects($this->once())
                          ->method('set')
                          ->with('message', 'my message.', true);

        $this->response->with('message', 'my message.');
    }

    public function test_withInput_method_sets_right_session_values()
    {
        $this->requestStub->method('all')
                          ->willReturn(['key1' => 'value1', 'key2' => 'value2']);

        $this->sessionStub->expects($this->once())
                          ->method('set')
                          ->with('php_input_session', ['key1' => 'value1', 'key2' => 'value2'], true);

        $this->response->withInput();
    }
}
