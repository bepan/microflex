<?php

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function test_setContentType_always_returns_dollar_this_with_correct_contentType()
    {
        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['set_header'])
                         ->getMock();

        $object = $response->setContentType('html');
        $this->assertSame($response, $object);
    }

    public function test_setContentType_with_html_calls_set_header_method_with_correct_args()
    {
        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['set_header'])
                         ->getMock();

        $response->expects($this->once())
                 ->method('set_header')
                 ->with('Content-Type', 'text/html');

        $response->setContentType('html');
    }

    public function test_setContentType_with_json_calls_set_header_method_with_correct_args()
    {
        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['set_header'])
                         ->getMock();

        $response->expects($this->once())
                 ->method('set_header')
                 ->with('Content-Type', 'application/json');

        $response->setContentType('json');
    }

    public function test_setContentType_with_unknown_type_throws_exception()
    {
        $response = new Microflex\Http\Response;

        $this->expectException(Exception::class);

        $response->setContentType('foo');
    }

    public function test_setCode_always_returns_dollar_this()
    {
        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['http_response_code'])
                         ->getMock();

        $object = $response->setCode(200);
        $this->assertSame($response, $object);  
    }

    public function test_setCode_with_200_calls_http_res_code_with_correct_arg()
    {
        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['http_response_code'])
                         ->getMock();

        $response->expects($this->once())
                 ->method('http_response_code')
                 ->with(200);

        $response->setCode(200); 
    }

    public function test_json_method_with_assoc_array_calls_right_methods_and_echo_out_correct_json_string()
    {
        $this->expectOutputString('{"name":"beto","age":24}');

        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['setCode', 'setContentType'])
                         ->getMock();

        $response->expects($this->once())
                 ->method('setCode')
                 ->with(201);

        $response->expects($this->once())
                 ->method('setContentType')
                 ->with('json');

        $response->json([
            'name' => 'beto',
            'age'  => 24
        ], 201);
    }

    public function test_send_method_with_content_sets_right_code_and_outputs_right_content()
    {
        $this->expectOutputString('<h1>hello world</h1>');

        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['setCode'])
                         ->getMock();

        $response->expects($this->once())
                 ->method('setCode')
                 ->with(200);

        $response->send('<h1>hello world</h1>');
    }

    public function test_render_method_renders_right_content()
    {
        $this->expectOutputString('<p>beto</p>');

        $response = $this->getMockBuilder(Microflex\Http\Response::class)
                         ->setMethods(['setContentType'])
                         ->getMock();

        $response->render(__DIR__ . '/index', ['var' => 'beto']);
    }
}
