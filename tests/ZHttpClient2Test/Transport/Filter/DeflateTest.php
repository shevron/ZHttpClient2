<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Transport\Filter;

use ZHttpClient2\Transport\Filter\Deflate;

class DeflateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Filter object
     *
     * @var ZHttpClient2\Transport\Filter\Deflate
     */
    protected $filter = null;

    public function setUp()
    {
        $this->filter = new Deflate();
    }

    public function tearDown()
    {
        $this->filter = null;
    }

    /**
     * Test filtering of entire content
     *
     * @param string $compressed
     * @param string $expected
     * @dataProvider contentProvider
     */
    public function testFilter($compressed, $expected)
    {
        $actual = $this->filter->filter($compressed);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test filtering in small chunks
     *
     * This immitates filtering openartion of an extremely problematic network
     * stream - data is read and passed 5 bytes at a time
     *
     * @param string $compressed
     * @param string $expected
     * @dataProvider contentProvider
     */
    public function testFilterSmallChunks($compressed, $expected)
    {
        $actual = '';
        while ($compressed) {
            $chunk = substr($compressed, 0, 5);
            $actual .= $this->filter->filter($chunk);
            $compressed = substr($compressed, 5);
        }

        $this->assertEquals($expected, $actual);
    }

    public function contentProvider()
    {
        return array(
            array(gzdeflate('hello world'), 'hello world'),
            array(gzdeflate(file_get_contents(__FILE__)), file_get_contents(__FILE__))
        );
    }
}
