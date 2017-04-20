<?php

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function setUp()
    {
        global $wasFooClosureCalled;

        $wasFooClosureCalled = false;
    }

    public function test_register_route_with_closure()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/home', $fooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasFooClosureCalled);
    }

    public function test_register_route_with_class_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/', 'FooController@index'); // Here is the key part

        $router->activate();

        $this->assertTrue(FooController::$wasIndexMethodCalled);
    }

    public function test_uri_pattern_matches_current_uri_with_query_strings()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home/?id=10&sid=11&foo=bar'; // Here is the key part

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home', $fooClosure);

        $router->activate();

        $this->assertTrue($wasFooClosureCalled);
    }

    public function test_uri_pattern_matches_current_uri_with_url_params()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home-route/100/my-slug/?id=10'; // Here is the key part

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home-route/:id/:slug', $fooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasFooClosureCalled);
    }
    
    public function test_that_DI_works_with_closure()
    {
        global $barClosure, $didDIworkWithClosure;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/home', $barClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($didDIworkWithClosure);
    }

    public function test_that_DI_works_with_class_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/home', 'FooController@create'); // Here is the key part

        $router->activate();

        $this->assertTrue(FooController::$didDIworkWithClassMethod);
    }

    public function test_that_constructor_deps_are_being_injected()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/home', 'BarController@index'); // Here is the key part

        $router->activate();

        $this->assertTrue(BarController::$wereConstructorDepsInjected);
    }

    public function test_that_recursive_DI_works()
    {
        global $zooClosure, $didRecursiveDIWork;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/home', $zooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($didRecursiveDIWork);    
    }

    public function test_register_middlewares_as_route_level()
    {
        global $fooClosure, $closureFooMiddleware, $wasFooClosureCalled, $wasClosureFooMiddCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/home', [$closureFooMiddleware, FooMiddleware::class], $fooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasClosureFooMiddCalled);
        $this->assertTrue(FooMiddleware::$wasCalled); 
        $this->assertTrue($wasFooClosureCalled); 
    }
}
