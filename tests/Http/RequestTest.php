<?php

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function test_all_method_returns_correct_data_when_get_verb()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['name' => 'Alberto', 'age' => 25];

        $request = $this->getMockBuilder(Microflex\Http\Request::class)
                        ->setMethods(['getHeaders'])
                        ->getMock();

        $this->assertEquals(['name' => 'Alberto', 'age' => 25], $request->all());
    }
}
