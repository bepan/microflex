<?php

use PHPUnit\Framework\TestCase;

class FooRepo
{
    public function message()
    {
        return 'foo repo message';
    }
}

class BarController
{
    public static $wereConstructorDepInjected = false;
    protected $fooRepo;

    public function __construct(FooRepo $fooRepo)
    {
        $this->fooRepo = $fooRepo;
    }

    public function index()
    {
        if ($this->fooRepo->message() === 'foo repo message') {
            self::$wereConstructorDepInjected = true;
        }
    }
}

class FooController
{
    public static $wasMethodCalled = false;
    public static $didRegularDIwork = false;

    public function index()
    {
        self::$wasMethodCalled = true;
    }

    public function create(Microflex\Http\Response $res, Microflex\Http\Request $req)
    {
        if (is_object($res) && is_object($req)) {
            self::$didRegularDIwork = true;
        }
    }
}

$wasClosureCalled = false;
$didRegularDIwork = false;

$closure = function() {
    global $wasClosureCalled;
    $wasClosureCalled = true;
};

$closure2 = function(Microflex\Http\Response $res, Microflex\Http\Request $req) {
    global $didRegularDIwork;

    if (is_object($res) && is_object($req)) {
        $didRegularDIwork = true;
    }
};

class RouterTest extends TestCase
{
    public function setUp()
    {
        global $wasClosureCalled;

        $wasClosureCalled = false;
    }

    public function test_register_route_with_closure()
    {
        global $closure, $wasClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home', $closure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasClosureCalled);
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

        $this->assertTrue(FooController::$wasMethodCalled);
    }

    public function test_uriPattern_matches_currentUri_with_query_strings()
    {
        global $closure, $wasClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home/?id=10&sid=11&foo=bar'; // Here is the key part

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home', $closure);

        $router->activate();

        $this->assertTrue($wasClosureCalled);
    }

    public function test_uriPattern_matches_currentUri_with_urlParams()
    {
        global $closure, $wasClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home-route/100/my-slug?'; // Here is the key part

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home-route/:id/:slug', $closure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasClosureCalled);
    }
    
    public function test_that_dependency_injection_works_with_closure()
    {
        global $closure2, $didRegularDIwork;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home', $closure2); // Here is the key part

        $router->activate();

        $this->assertTrue($didRegularDIwork);
    }

    public function test_that_dependency_injection_works_with_classMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home', 'FooController@create'); // Here is the key part

        $router->activate();

        $this->assertTrue(FooController::$didRegularDIwork);
    }

    public function test_that_constructor_dependencies_are_being_injected()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                           ->setMethods(['setInputSession'])
                           ->getMock();

        $router->get('/home', 'BarController@index'); // Here is the key part

        $router->activate();

        $this->assertTrue(BarController::$wereConstructorDepInjected);
    }
}
