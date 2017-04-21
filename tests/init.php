<?php

// CLOSURES
// =================================================================
$wasFooClosureCalled = false;

$didDIworkWithClosure = false;

$didRecursiveDIWork = false;

$fooClosure = function() {

    global $wasFooClosureCalled;

    $wasFooClosureCalled = true;
};

$barClosure = function(Microflex\Http\Request $req, Microflex\Http\Request $res) {

    global $didDIworkWithClosure;

    if (is_object($req) && is_object($res)) {

        $didDIworkWithClosure = true;
    }
};

$zooClosure = function(FooRepo $fooRepo) {

    global $didRecursiveDIWork;

    if ($fooRepo->getDBConn() === 'db connection ready') {

        $didRecursiveDIWork = true;
    }
};
// -----------------------------------------------------------------

// MIDDLEWARE CONTROLLERS
// =================================================================
class FooController
{
    public static $wasIndexMethodCalled = false;

    public static $didDIworkWithClassMethod = false;

    public function index()
    {
        self::$wasIndexMethodCalled = true;
    }

    public function create(Microflex\Http\Response $res, Microflex\Http\Request $req)
    {
        if (is_object($res) && is_object($req)) {

            self::$didDIworkWithClassMethod = true;
        }
    }
}

class BarController
{
    public static $wereConstructorDepsInjected = false;

    protected $req;
    
    protected $res;
    
    public function __construct(Microflex\Http\Request $req, Microflex\Http\Request $res) 
    {
    	$this->req = $req;

    	$this->res = $res;  
    }

    public function index() 
    {
        if (is_object($this->req) && is_object($this->res)) {

            self::$wereConstructorDepsInjected = true;
        } 
    }
}

class FooRepo
{
	protected $dbConn;
    public function __construct(DBConn $dbConn) {
        $this->dbConn = $dbConn;
    }

    public function getDBConn() {
        return $this->dbConn->message();
    }
}

class DBConn
{
    public function message() {
        return 'db connection ready';
    }
}
// -----------------------------------------------------------------

// PURE MIDDLEWARES
// =================================================================
$wasClosureFooMiddCalled = false;

class FooMiddleware
{
	public static $wasCalled = false;
    public function run(Callable $next) {
        self::$wasCalled = true;
        $next();
    }
}

class BarMiddleware
{
    public static $wasCalled = false;
    public function run(Callable $next) {
        self::$wasCalled = true;
        $next();
    }
}

class ZooMiddleware
{
    public static $wasCalled = false;
    public function run(Callable $next) {
        self::$wasCalled = true;
        $next();
    }
}

$closureFooMiddleware = function(Callable $next) {
    global $wasClosureFooMiddCalled;
    $wasClosureFooMiddCalled = true;
    $next();
};
// -----------------------------------------------------------------