<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Entity;

use ZHttpClient2\Entity\UrlEncodedFormData as Entity;
use ZHttpClient2\Request;
use Zend\Stdlib\Parameters;

class UrlEncodedFormDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Entity object
     *
     * @var Zend\Http\Entity\UrlEncodedFormData
     */
    protected $entity = null;

    public function setUp()
    {
        $this->entity = new Entity();
    }

    public function tearDown()
    {
        $this->entity = null;
    }

    public function testFormDataIsNull()
    {
        $this->assertNull($this->entity->getFormData());
    }

    public function testSetGetFormData()
    {
        $request = new Request();
        $this->entity->setFormData($request->getPost());
        $this->assertSame($request->getPost(), $this->entity->getFormData());
    }

    public function testReadNoFormDataReturnsFalse()
    {
        $this->assertFalse($this->entity->read());
    }

    public function testReadEmptyFormDataReturnsFalse()
    {
        $this->entity->setFormData(new Parameters());
        $this->assertFalse($this->entity->read());
    }

    /**
     * @dataProvider formDataProvider
     */
    public function testReadContent($data, $expected)
    {
        $this->entity->setFormData(new Parameters($data));

        $actual = '';
        while (($chunk = $this->entity->read()) != false) $actual .= $chunk;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formDataProvider
     */
    public function testToString($data, $expected)
    {
        $this->entity->setFormData(new Parameters($data));
        $this->assertEquals($expected, $this->entity->toString());
    }

    /**
     * @dataProvider formDataProvider
     */
    public function testToStringMagic($data, $expected)
    {
        $this->entity->setFormData(new Parameters($data));
        $this->assertEquals($expected, (string) $this->entity);
    }

    public function testRewind()
    {
        $this->entity->setFormData(new Parameters(array('a' => 'b', 'c' => 'd')));

        // First read should return a string
        $expected = $this->entity->read();
        $this->assertNotEmpty($expected);

        // Read until nothing is left
        while($this->entity->read());

        // Next reads should reutnr false
        $this->assertFalse($this->entity->read());

        // After rewining we should be able to read again
        $this->entity->rewind();
        $this->assertEquals($expected, $this->entity->read());
    }

    /**
     * Data Providers
     */

    public function formDataProvider()
    {
        return array(
            array(
                array(
                    'foo'   => 'bar',
                    'hello' => 'cruel world',
                    'var'   => 'with+plus',
                ),
                'foo=bar&hello=cruel%20world&var=with%2Bplus'
            )
        );
    }
}
