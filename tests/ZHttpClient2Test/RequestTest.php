<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Http
 */

namespace ZHttpClient2Test;

use ZHttpClient2\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function testRequestFromStringFactoryCreatesValidRequest()
    {
        $string = "GET /foo HTTP/1.1\r\n\r\nSome Content";
        $request = Request::fromString($string);

        $this->assertEquals(Request::METHOD_GET, $request->getMethod());
        $this->assertEquals('/foo', $request->getUri()->toString());
        $this->assertEquals(Request::VERSION_11, $request->getVersion());
        $this->assertEquals('Some Content', $request->getContent());
    }

    public function testRequestUsesParametersContainerByDefault()
    {
        $request = new Request();
        $this->assertInstanceOf('Zend\Stdlib\Parameters', $request->getQuery());
        $this->assertInstanceOf('Zend\Stdlib\Parameters', $request->getPost());
    }

    public function testRequestAllowsSettingOfParameterContainer()
    {
        $request = new Request();
        $p = new \Zend\Stdlib\Parameters();
        $request->setQuery($p);
        $request->setPost($p);

        $this->assertSame($p, $request->getQuery());
        $this->assertSame($p, $request->getPost());
    }

    public function testRequestPersistsRawBody()
    {
        $request = new Request();
        $request->setContent('foo');
        $this->assertEquals('foo', $request->getContent());
    }

    public function testRequestUsesHeadersContainerByDefault()
    {
        $request = new Request();
        $this->assertInstanceOf('Zend\Http\Headers', $request->getHeaders());
    }

    public function testRequestCanSetHeaders()
    {
        $request = new Request();
        $headers = new \Zend\Http\Headers();

        $ret = $request->setHeaders($headers);
        $this->assertInstanceOf('Zend\Http\Request', $ret);
        $this->assertSame($headers, $request->getHeaders());
    }

    public function testRequestCanSetAndRetrieveKnownMethod()
    {
        $request = new Request();
        $request->setMethod('POST');
        $this->assertEquals('POST', $request->getMethod());
    }

    public function testRequestCanAlwaysForcesUppecaseMethodName()
    {
        $request = new Request();
        $request->setMethod('get');
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testRequestCanSetAndRetrieveUri()
    {
        $request = new Request();
        $request->setUri('/foo');
        $this->assertInstanceOf('Zend\Uri\Uri', $request->getUri());
        $this->assertEquals('/foo', $request->getUri()->toString());
    }

    public function testRequestSetUriWillThrowExceptionOnInvalidArgument()
    {
        $request = new Request();

        $this->setExpectedException('Zend\Uri\Exception\InvalidArgumentException', 'Expecting a string or a URI object, received ');
        $request->setUri(new \stdClass());
    }

    public function testRequestCanSetAndRetrieveVersion()
    {
        $request = new Request();
        $this->assertEquals('1.1', $request->getVersion());
        $request->setVersion(Request::VERSION_10);
        $this->assertEquals('1.0', $request->getVersion());
    }

    public function testRequestSetVersionWillThrowExceptionOnInvalidArgument()
    {
        $request = new Request();

        $this->setExpectedException('Zend\Http\Exception\InvalidArgumentException', 'not a valid version');
        $request->setVersion('1.2');
    }

    /**
     * @dataProvider getMethods
     */
    public function testRequestMethodCheckWorksForKnownMethods($methodName)
    {
        $request = new Request;
        $request->setMethod($methodName);

        foreach ($this->getMethods(false, $methodName) as $testMethodName => $testMethodValue) {
            $this->assertEquals($testMethodValue, $request->{'is' . $testMethodName}());
        }
    }

    /**
     * @dataProvider validMethodProvider
     */
    public function testSetGetCustomMethod($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->assertEquals($method, $request->getMethod());
    }

    /**
     * @dataProvider invalidMethodProvider
     * @expectedException Zend\Http\Exception\InvalidArgumentException
     */
    public function testExceptionOnInvalidMethod($method)
    {
        $request = new Request();
        $request->setMethod($method);
    }

    public function testRequestCanBeCastToAString()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->setUri('/');
        $request->setContent('foo=bar&bar=baz');
        $this->assertEquals("GET / HTTP/1.1\r\n\r\nfoo=bar&bar=baz", $request->toString());
    }

    public function testRequestIsXmlHttpRequest()
    {
        $request = new Request();
        $this->assertFalse($request->isXmlHttpRequest());

        $request = new Request();
        $request->getHeaders()->addHeaderLine('X_REQUESTED_WITH', 'FooBazBar');
        $this->assertFalse($request->isXmlHttpRequest());

        $request = new Request();
        $request->getHeaders()->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->assertTrue($request->isXmlHttpRequest());
    }

    public function testRequestIsFlashRequest()
    {
        $request = new Request();
        $this->assertFalse($request->isFlashRequest());

        $request = new Request();
        $request->getHeaders()->addHeaderLine('USER_AGENT', 'FooBazBar');
        $this->assertFalse($request->isFlashRequest());

        $request = new Request();
        $request->getHeaders()->addHeaderLine('USER_AGENT', 'Shockwave Flash');
        $this->assertTrue($request->isFlashRequest());
    }

    /**
     * PHPUnit Data Provider for known request methods
     *
     * @param $providerContext
     * @param  null  $trueMethod
     * @return array
     */
    public function getMethods($providerContext, $trueMethod = null)
    {
        $refClass = new \ReflectionClass('Zend\Http\Request');
        $return = array();
        foreach ($refClass->getConstants() as $cName => $cValue) {
            if (substr($cName, 0, 6) == 'METHOD') {
                if ($providerContext) {
                    $return[] = array($cValue);
                } else {
                    $return[strtolower($cValue)] = ($trueMethod == $cValue) ? true : false;
                }
            }
        }

        return $return;
    }

    /**
     * Provider for valid HTTP method names
     *
     * The RFC defines valid HTTP request methods as 'token' which means one or
     * more US ASCII characters except for CTLs (ASCII 0x00 - 0x19, 0x7f) and
     * separators as defined in the RFC
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec2.html#sec2.2
     */
    public static function validMethodProvider()
    {
        return array(
            array('GET'),
            array('GETPROPS'),
            array('PATCH'),
            array('FooBar'),
            array('FOO_BAR'),
            array('_POST'),
            array('FOO-BAR'),
            array('QWE*')
        );
    }

    /**
     * Provider for invalid HTTP method names
     *
     */
    public static function invalidMethodProvider()
    {
        $separators = str_split("()<>@,;:\\\"/[]?={}", 1);
        $ret = array(
            array(null),
            array(new \stdClass()),
            array(array()),
            array(3),
            array(''),
            array('FOO BAR'),
            array("foo\t"),
            array("foo\0"),
            array("תביא"),
        );

        foreach ($separators as $c) {
            $ret[] = array("FOO{$c}BAR");
        }

        return $ret;
    }
}
