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
            array(gzencode('hello world'), 'hello world'),
            array(gzencode(file_get_contents(__FILE__)), file_get_contents(__FILE__))
        );
    }
}
