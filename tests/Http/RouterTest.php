<?php

use PHPUnit\Framework\TestCase;

class TestClass {
    protected $testClass2;
    public function __construct(TestClass2 $testClass2)
    {
        $this->testClass2 = $testClass2;
    }

    public function index() { return $this->testClass2->index(); } 
}

class TestClass2 { public function index() { return 'test class 2'; } }

class RouterTest extends TestCase
{
    public function test_register_route_as_closure()
    {
        $router = new Microflex\Http\Router;

        $router->get('/home', function() {});
        
        $routes = $router->getRoutes();

        $this->assertCount(1, $routes);

        $route = $routes[0];

        $this->assertEquals('get', $route['method']);
        
        $this->assertCount(1, $route['middlewares']);
        
        $this->assertTrue($route['middlewares'][0] instanceof Closure);
    }

    public function test_register_route_as_class_method()
    {
        $router = new Microflex\Http\Router;

        $router->post('/about', 'TestClass@index');
        
        $routes = $router->getRoutes();

        $this->assertCount(1, $routes);

        $route = $routes[0];

        $this->assertEquals('post', $route['method']);
        
        $this->assertCount(1, $route['middlewares']);
        
        $this->assertInternalType('array', $route['middlewares'][0]);
        
        $this->assertEquals('TestClass', $route['middlewares'][0][0]);
        
        $this->assertEquals('index', $route['middlewares'][0][1]);
    }

    public function test_route_uriPattern_matches_against_paths()
    {
        $router = new Microflex\Http\Router;

        $router->get('/home', function() {});

        $route = $router->getRoutes()[0];

        $this->assertEquals(1, preg_match($route['pattern'], '/home'));
        
        $this->assertEquals(1, preg_match($route['pattern'], '/home/'));
        
        $this->assertEquals(1, preg_match($route['pattern'], '/home?id=10&sid=11&fid=12'));
        
        $this->assertEquals(1, preg_match($route['pattern'], '/home?id'));
        
        $this->assertEquals(1, preg_match($route['pattern'], '/home?id&sid'));
        
        $this->assertEquals(1, preg_match($route['pattern'], '/home?id=&sid='));
    }

    public function test_route_urlParams_are_registred()
    {
        $router = new Microflex\Http\Router;

        $router->get('/home/:id/:slug', function() {});

        $route = $router->getRoutes()[0];

        $this->assertEquals(['id' => 2, 'slug' => 3], $route['urlParams']);
    }

    public function test_route_uriPattern_matches_against_path_with_params()
    {
        $router = new Microflex\Http\Router;

        $router->get('/home/:id', function() {});

        $route = $router->getRoutes()[0];

        $this->assertEquals(1, preg_match($route['pattern'], '/home/100?id=10&sid=11&fid=12'));
    }

    public function test_routes_are_registering_their_correct_middlewares()
    {
        $router = new Microflex\Http\Router;

        $router->use(function() {});

        $router->get('/', function() {});

        $router->use(function() {});
        $router->use(function() {});

        $router->get('/about', function() {});

        $routes = $router->getRoutes();
        $route1 = $routes[0];
        $route2 = $routes[1];

        $this->assertCount(2, $route1['middlewares']);
        $this->assertCount(4, $route2['middlewares']);
    }

}
