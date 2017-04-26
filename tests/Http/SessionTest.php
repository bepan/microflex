<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
	protected $session;

	public function setUp()
	{
		global $wasSessionDestroyCalled;

        $wasSessionDestroyCalled = false;

        $this->session = $this->getMockBuilder(Microflex\Http\Session::class)
                              ->setMethods(['start', 'sessionDestroy'])
                              ->getMock();
	}

	public function tearDown()
	{
		//
	    $_SESSION = [];
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

	public function test_all_method_returns_all_sanitized_session_array()
	{
	    $this->session->set('color', 'blue');
	    $this->session->set('icecream', '<a>banana<a>');

	    $this->assertEquals(['color' => 'blue', 'icecream' => '&amp;lt;a&amp;gt;banana&amp;lt;a&amp;gt;'], $this->session->all());
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
	    $this->session->set('color', 'blue');
	    $this->session->set('band', 'tbdm');
	    
        $this->session->expects($this->once())
                      ->method('sessionDestroy');

	    $this->session->destroy();

	    $this->assertEquals([], $this->session->all());
	}
}
