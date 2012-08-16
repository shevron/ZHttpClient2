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
        $actual = md5($actual);
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

        $actual = md5($actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provide compressed content for testing along with the MD5 sum of the
     * uncompressed original content to verify the output against
     *
     * @return array
     */
    public function contentProvider()
    {
        return array(
            array(gzdeflate('hello world'), md5('hello world')),
            array(gzdeflate(file_get_contents(__FILE__)), md5(file_get_contents(__FILE__))),
            array(file_get_contents('data/deflate_data_01'), '0b13cb193de9450aa70a6403e2c9902f'),
            array(file_get_contents('data/deflate_data_02_iis'), 'd82c87e3d5888db0193a3fb12396e616'),
        );
    }
}
