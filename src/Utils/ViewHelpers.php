<?php

define('WITH_INPUT', true);

function partial($path, array $data = [])
{
    foreach ($data as $key => $value) {

        ${$key} = $value;
    }
    
    require("$path.php");
}

function input($name)
{
    global $php_input_session;

    return $php_input_session[$name] ?? null;
}

function session($name)
{
    global $php_flashed_session;

    return $php_flashed_session[$name] ?? null;
}