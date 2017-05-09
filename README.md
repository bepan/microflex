[Go to PHP code](#app-code)

# Microflex

- Register routes with closures midd.
- Register routes with a class method midd.
- Check that path and method match register routes.
- Recursive Dependency Injection as a singleton.
- Match the current uri with the register uri pattern.
- Register middlewares as route level.
- Register 404 middlewares.
- Register nested middlewares as group level.
- Register nested prefixes to group route names.
- Request Class: all, input, getCookie(s), (g/s)etSessionValue, getAllSession, unsetSessionValue, destroySession.
- Response Class: setContentType, json, send, render, redirect, setCookie, unsetCookie, setCode.
- Helper functions: session, input, has_session

# Future
- RouteMethod Class.
- Session Class.
- Cookie Class.
- Constructor Middlewares.

# Missings
- Fix Uri spliting by '/'.
- Populate App class.
- process flashed session values at bootstrap.
- Refactor view helper global functions.
- Generate documentation on this file.

# App Code
```php
<?php
namespace Microflex\Http;

class App
{
    protected $router;
    
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
```
