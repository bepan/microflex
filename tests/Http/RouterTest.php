<?php

use PHPUnit\Framework\TestCase;

class FooController
{
    public static $methodWasCalled = false;

    public function index()
    {
        self::$methodWasCalled = true;
    }
}

$closureCalled = false;

$closure = function() {
    global $closureCalled;
    $closureCalled = true;
};

class RouterTest extends TestCase
{
    public function test_register_route_with_closure()
    {
        global $closure, $closureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $stubRouter = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $stubRouter->get('/home', $closure); // Here is the key part

        $stubRouter->activate();

        $this->assertTrue($closureCalled);
    }

    public function test_register_route_with_class_method()
    {
        global $closure, $closureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $stubRouter = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $stubRouter->get('/', 'FooController@index'); // Here is the key part

        $stubRouter->activate();

        $this->assertTrue(FooController::$methodWasCalled);
    }
}
