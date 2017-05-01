<?php

use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    public function setUp()
    {
    	$this->security = new Microflex\Utils\Security;

        $this->cookie = $this->getMockBuilder(Microflex\Http\Cookie::class)
                             ->setConstructorArgs([$this->security])
                             ->setMethods(['setCookie'])
                             ->getMock();

        $_COOKIE = [
            'color' => 'purple',
            'colors' => 'purple,blue,black',
        ];
    }

    public function test_cookie_set_method_sets_right_values_for_the_cookie()
    {
        $this->cookie->expects($this->once())
                     ->method('setCookie')
                     ->with('color', 'blue', 0, '/', '', false, false);

        $this->cookie->set('color', 'blue');
    }

    public function test_cookie_unset_method_unsets_right_cookie_correctly()
    {
        $this->cookie->expects($this->once())
                     ->method('setCookie')
                     ->with('color', '', 1, '/', '', false, false);

        $this->cookie->unset('color');
    }

    public function test_cookie_get_method_returns_right_cookie()
    {
        $this->assertEquals('purple', $this->cookie->get('color'));
    }

    public function test_cookie_all_method_returns_all_cookies_if_values()
    {
        $this->assertEquals([
            'color' => 'purple',
            'colors' => 'purple,blue,black',
        ], $this->cookie->all());
    }

    public function test_cookie_all_method_returns_empty_array_if_no_values()
    {
        $_COOKIE = [];

        $this->assertEquals([], $this->cookie->all());
    }
}












