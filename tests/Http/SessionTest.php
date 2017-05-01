<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
	public function setUp()
	{
        $this->security = new Microflex\Utils\Security;

        $this->cookieStub = $this->createMock(Microflex\Http\Cookie::class);

        $this->session = $this->getMockBuilder(\Microflex\Http\Session::class)
                              ->setConstructorArgs([$this->security, $this->cookieStub])
                              ->setMethods(['start'])
                              ->getMock();
	}

	public function tearDown()
	{
	    $_SESSION = [];
	}

	public function test_start_method_call_session_start_if_not_started()
	{
        $this->session = $this->getMockBuilder(\Microflex\Http\Session::class)
                              ->setConstructorArgs([$this->security, $this->cookieStub])
                              ->setMethods(['session_status', 'session_start'])
                              ->getMock();

        $this->session->method('session_status')
                      ->willReturn(1);

        $this->session->expects($this->once())
                      ->method('session_start');

        $this->session->start();
	}

	public function test_start_method_not_to_call_session_start_if_already_started()
	{
        $this->session = $this->getMockBuilder(\Microflex\Http\Session::class)
                              ->setConstructorArgs([$this->security, $this->cookieStub])
                              ->setMethods(['session_status', 'session_start'])
                              ->getMock();

        $this->session->method('session_status')
                      ->willReturn(2);

        $this->session->expects($this->never())
                      ->method('session_start');

        $this->session->start();
	}

	public function test_setting_getting_session_value()
	{
	    $this->session->set('color', 'blue');

	    $this->assertEquals('blue', $this->session->get('color'));

	    $this->assertEquals(false, $_SESSION['color'][1]);
	}

	public function test_getting_session_value_that_not_exist()
	{
	    $this->session->set('color', 'blue');
	    $this->session->set('band', 'tbdm');

	    $this->assertEquals(null, $this->session->get('icecream'));
	}

	public function test_all_method_returns_all_session_array()
	{
	    $this->session->set('color', 'blue');
	    $this->session->set('icecream', 'banana');

	    $this->assertEquals(['color' => 'blue', 'icecream' => 'banana'], $this->session->all());
	}

	public function test_unset_method_removes_session_value()
	{
	    $this->session->set('color', 'blue');
	    $this->session->set('band', 'tbdm');

	    $this->session->unset('band');

	    $this->assertEquals(null, $this->session->get('band'));
	}

	public function test_destroy_method_removes_the_whole_session()
	{
        $this->session = $this->getMockBuilder(\Microflex\Http\Session::class)
	                          ->setConstructorArgs([$this->security, $this->cookieStub])
                              ->setMethods(['start', 'session_destroy'])
                              ->getMock();

	    $this->session->set('color', 'blue');
	    $this->session->set('band', 'tbdm');


	    $this->cookieStub->expects($this->once())
	                     ->method('unset')
	                     ->with(session_name());

        $this->session->expects($this->once())
                      ->method('session_destroy');

	    $this->session->destroy();

	    $this->assertEquals([], $this->session->all());
	}
}
