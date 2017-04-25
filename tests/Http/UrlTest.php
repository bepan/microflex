<?php

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    protected $url;

    public function setUp()
    {
        $_SERVER['QUERY_STRING'] = 'name=<h1>Albert</h1>&girlfriend=stephanie';

        $_SERVER['REQUEST_URI'] = '/path/to/<h1>home<h1>';

        $this->url = new Microflex\Http\Url([
            'id' => 1,
            'slug' => 3
        ]);
    }

    public function test_query_method_with_name_returns_correct_value()
    {
        $this->assertEquals('stephanie', $this->url->query('girlfriend'));
    }

    public function test_queries_method_returns_correct_values()
    {
        $this->assertEquals(['name' => '&lt;h1&gt;Albert&lt;/h1&gt;', 'girlfriend' => 'stephanie'], $this->url->queries());
    }

    public function test_param_method_with_name_returns_correct_value()
    {
        $this->assertEquals('path', $this->url->param('id'));
    }

    public function test_params_method_returns_correct_values()
    {
        $this->assertEquals(['id' => 'path', 'slug' => '&lt;h1&gt;home&lt;h1&gt;'], $this->url->params());
    }
}
