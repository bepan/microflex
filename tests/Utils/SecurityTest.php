<?php

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    public function setUp()
    {
        $this->security = new Microflex\Utils\Security;
    }

    public function test_sanitize_method_with_string_will_return_sanitized_string()
    {
        $this->assertEquals(
            '&lt;script&gt;beto&lt;/script&gt;', 
            $this->security->sanitize('<script>beto</script>')
        );
    }

    public function test_sanitize_method_with_null_will_return_null()
    {
        $this->assertEquals(
            null, 
            $this->security->sanitize(null)
        );
    }

    public function test_sanitize_method_with_single_array()
    {
        $singleArr = ['<p>hola</p>', 'fail', '<b>unsanitized</b>'];

        $this->assertEquals([

            '&lt;p&gt;hola&lt;/p&gt;', 
            'fail', 
            '&lt;b&gt;unsanitized&lt;/b&gt;'

        ], $this->security->sanitize($singleArr));
    }

    public function test_sanitize_method_with_multi_array()
    {
        $multiArr = [
            'key1' => '<p>hola</p>', 
            'key2' => ['<p>beto</p>', 'lis'], 
            'key3' => null
        ];

        $this->assertEquals([

            'key1' => '&lt;p&gt;hola&lt;/p&gt;', 
            'key2' => ['&lt;p&gt;beto&lt;/p&gt;', 'lis'], 
            'key3' => null

        ], $this->security->sanitize($multiArr));
    }
}
