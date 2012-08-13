<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Entity;

use ZHttpClient2\Entity\String as StringEntity;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Entity object
     *
     * @var ZHttpClient2\Entity\String
     */
    protected $entity = null;

    public function setUp()
    {
        $this->entity = new StringEntity();
    }

    public function tearDown()
    {
        $this->entity = null;
    }

    public function testFromToString()
    {
        $expected = "Hello, this is a test\n  How does it look?";
        $this->entity->fromString($expected);
        $this->assertEquals($expected, $this->entity->toString());
    }

    public function testFromToStringMagic()
    {
        $expected = "Hello, this is a test\n  How does it look?";
        $this->entity->fromString($expected);
        $this->assertEquals($expected, (string) $this->entity);
    }

    public function testWriteContentInChunks()
    {
        $expected = "Hello, this is a test\n  How does it look?";
        $chunks = str_split($expected, 5);
        foreach ($chunks as $c) {
            $this->entity->write($c);
        }

        $this->assertEquals($expected, $this->entity->toString());
    }

    public function testReadContent()
    {
        $expected = "Hello, this is a test\n  How does it look?";
        $this->entity->fromString($expected);
        $actual = '';
        while (($chunk = $this->entity->read()) != false) $actual .= $chunk;
        $this->assertEquals($expected, $actual);
    }

    public function testRewind()
    {
        $expected = "Hello, this is a test\n  How does it look?";
        $this->entity->fromString($expected);

        // First read should return expected string
        $this->assertEquals($expected, $this->entity->read());

        // Further reads should return false
        $this->assertFalse($this->entity->read());
        $this->assertFalse($this->entity->read());

        // After rewining we should get expected string again
        $this->entity->rewind();
        $this->assertEquals($expected, $this->entity->read());
    }
}
