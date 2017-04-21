<?php

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function setUp()
    {
        global $wasFooClosureCalled, $wasClosureFooMiddCalled;

        $wasFooClosureCalled = false;
        $wasClosureFooMiddCalled = false;
        $didDIworkWithClosure = false;
        $didRecursiveDIWork = false;

        FooController::$wasIndexMethodCalled = false;
        FooController::$didDIworkWithClassMethod = false;
        BarController::$wereConstructorDepsInjected = false;

        FooMiddleware::$wasCalled = false;
        BarMiddleware::$wasCalled = false;
        ZooMiddleware::$wasCalled = false;
    }

    public function test_register_get_route_with_closure()
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

    public function test_register_post_route_with_closure()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->post('/home', $fooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasFooClosureCalled);
    }

    public function test_register_put_route_with_closure()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->put('/home', $fooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasFooClosureCalled);
    }

    public function test_register_delete_route_with_closure()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->delete('/home', $fooClosure); // Here is the key part

        $router->activate();

        $this->assertTrue($wasFooClosureCalled);
    }

    public function test_register_patch_route_with_closure()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->patch('/home', $fooClosure); // Here is the key part

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

    public function test_all_middlewares_are_called_as_group()
    {
        global $fooClosure, $wasFooClosureCalled, $closureFooMiddleware, $wasClosureFooMiddCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();
        
        $router->group(['middleware' => FooMiddleware::class], function() use ($router, $closureFooMiddleware, $fooClosure) {
            
            $router->group(['middleware' => [BarMiddleware::class, ZooMiddleware::class]], function() use ($router, $closureFooMiddleware, $fooClosure) {

                $router->get('/home', [$closureFooMiddleware], $fooClosure); // Here is the key part
            
            });
        });

        $router->activate();
        
        $this->assertTrue(FooMiddleware::$wasCalled);
        $this->assertTrue(BarMiddleware::$wasCalled);
        $this->assertTrue(ZooMiddleware::$wasCalled);
        $this->assertTrue($wasClosureFooMiddCalled); 
        $this->assertTrue($wasFooClosureCalled);    
    }

    public function test_some_middlewares_are_called_as_group()
    {
        global $fooClosure, $wasFooClosureCalled, $closureFooMiddleware, $wasClosureFooMiddCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();
        
        $router->group(['middleware' => FooMiddleware::class], function() use ($router, $closureFooMiddleware, $fooClosure) {
            
            $router->group(['middleware' => [BarMiddleware::class, ZooMiddleware::class]], function() use ($router, $closureFooMiddleware, $fooClosure) {

                $router->get('/home', [$closureFooMiddleware], $fooClosure); // Here is the key part
            
            });

            $router->get('/foo', [$closureFooMiddleware], $fooClosure); // Here is the key part
        });

        $router->activate();
        
        $this->assertTrue(FooMiddleware::$wasCalled);
        $this->assertFalse(BarMiddleware::$wasCalled);
        $this->assertFalse(ZooMiddleware::$wasCalled);
        $this->assertTrue($wasClosureFooMiddCalled); 
        $this->assertTrue($wasFooClosureCalled);    
    }

    public function test_all_prefixes_are_being_prepended_to_uri()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/prefix/admin/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();
        
        $router->group(['prefix' => '/prefix'], function() use ($router, $fooClosure) {
            
            $router->group(['prefix' => '/admin'], function() use ($router, $fooClosure) {

                $router->get('/home', $fooClosure); // Here is the key part
            
            });
        });

        $router->activate();
 
        $this->assertTrue($wasFooClosureCalled);    
    }

    public function test_some_prefixes_are_being_prepended_to_uri()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/prefix/about';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();
        
        $router->group(['prefix' => '/prefix'], function() use ($router, $fooClosure) {
            
            $router->group(['prefix' => '/admin'], function() use ($router, $fooClosure) {

                $router->get('/home', $fooClosure); // Here is the key part
            
            });

            $router->get('/about', $fooClosure);
        });

        $router->activate();
 
        $this->assertTrue($wasFooClosureCalled);    
    }

    public function test_register_404_middlewares()
    {
        global $fooClosure, $wasFooClosureCalled;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/home';

        $router = $this->getMockBuilder(Microflex\Http\Router::class)
                       ->setMethods(['setInputSession'])
                       ->getMock();

        $router->get('/about', $fooClosure); // Here is the key part

        $router->handle404(FooMiddleware::class, BarMiddleware::class);

        $router->activate();

        $this->assertTrue(FooMiddleware::$wasCalled);
        $this->assertTrue(BarMiddleware::$wasCalled);
    }
}
