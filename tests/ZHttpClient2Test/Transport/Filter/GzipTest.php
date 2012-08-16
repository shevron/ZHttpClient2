<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Transport\Filter;

use ZHttpClient2\Transport\Filter\Gzip;

class GzipTest extends DeflateTest
{
    public function setUp()
    {
        $this->filter = new Gzip();
    }

    public function contentProvider()
    {
        return array(
            array(gzencode('hello world'), md5('hello world')),
            array(gzencode(file_get_contents(__FILE__)), md5(file_get_contents(__FILE__))),
            array(file_get_contents('data/gzip_data_01_wikipedia'), '9e3237759e252098412e642c3a23ba7e'),
        );
    }
}
