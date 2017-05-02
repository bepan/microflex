<?php

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function setUp()
    {
        $_SERVER['QUERY_STRING'] = 'name=Albert&girlfriend=stephanie';

        $_SERVER['REQUEST_URI'] = '/path/to/home?id=10';

        $this->security = new Microflex\Utils\Security;

        $this->url = new Microflex\Http\Url($this->security);
    }

    public function test_query_method_with_name_returns_correct_value()
    {
        $this->assertEquals('stephanie', $this->url->query('girlfriend'));
    }

    public function test_queries_method_returns_correct_values()
    {
        $this->assertEquals(['name' => 'Albert', 'girlfriend' => 'stephanie'], $this->url->queries());
    }

    public function test_setUrlParams_and_get_all_url_params()
    {
        $this->url->setUrlParams([
            'id' => 1,
            'slug' => 3
        ]);

        $this->assertEquals(['id' => 'path', 'slug' => 'home'], $this->url->params());
    }

    public function test_setUrlParams_and_get_single_url_param()
    {
        $this->url->setUrlParams([
            'id' => 1,
            'slug' => 3
        ]);

        $this->assertEquals('home', $this->url->param('slug'));
    }
}
