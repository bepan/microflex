<?php

require_once __DIR__ . '/../vendor/autoload.php';

$router = new Betopan\Http\Router();

$router->get('/', function () {
    echo 'root page.';
});

$router->get('/about', function () {
    echo 'about page.';
});

$router->activate();

