<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Entity;

use ZHttpClient2\Entity\SmartBuffer;

class SmartBufferTest extends StringTest
{
    /**
     * Entity object
     *
     * @var Zend\Http\Entity\SmartBuffer
     */
    protected $entity = null;

    public function setUp()
    {
        $this->entity = new SmartBuffer();
    }

    public function testWriteReadLargeData()
    {
        // Write 20mb data
        for ($i = 0; $i < 10240; $i++) {
            $this->entity->write(str_repeat("\0", 2048));
        }

        // Rewing and read returned bytes
        $this->entity->rewind();
        $bytes = 0;
        while (($chunk = $this->entity->read()) != false) $bytes += strlen($chunk);

        $this->assertEquals(10240 * 2048, $bytes);
    }
}
