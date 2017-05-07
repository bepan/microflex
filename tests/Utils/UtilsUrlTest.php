<?php

use PHPUnit\Framework\TestCase;

class UtilsUrlTest extends TestCase
{
    public function setUp()
    {
        $this->utilsUrl = new Microflex\Utils\Url;
    }

    public function test_splitUrl_method() 
    {
        $this->assertEquals(['', 'path', ':to', '<p>alert</p>', 'ho?id=<b>10</b>&sid=foo'], $this->utilsUrl->splitUri('/path/:to/<p>alert</p>/ho?id=<b>10</b>&sid=foo'));
    }
}
