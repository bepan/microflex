<?php

define('WITH_INPUT', true);

function partial($path)
{
    require($path);
}

function input($name)
{
    global $php_input_session;

    return $php_input_session[$name] ?? null;
}

function session($name)
{
    session_start();

    $sessionValue = $_SESSION[$name] ?? null;

    if ($sessionValue === null) return null;

    if ($sessionValue[1]) {

        unset($_SESSION[$name]);
    }

    return $sessionValue[0];
}

function has_session($name)
{
    session_start();

    return isset($_SESSION[$name]);
}