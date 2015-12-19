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

use ZHttpClient2\Client;
use ZHttpClient2\ClientOptions;
use ZHttpClient2\Transport\Options as TransportOptions;
use ZHttpClient2\Transport\Test as TestTransport;
use ZHttpClient2\Transport\Socket as SocketTransport;
use ZHttpClient2\Request;
use ZHttpClient2\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * HTTP client object
     *
     * @var ZHttpClient2\Client
     */
    protected $client = null;

    /**
     * HTTP transport object
     *
     * @var ZHttpClient2\Transport\Test
     */
    protected $transport = null;

    public function setUp()
    {
        $this->transport = new TestTransport();
        $this->transport->setDefaultResponse(Response::fromString("HTTP/1.1 200 Ok\r\nContent-length: 0\r\n\r\n"));

        $this->client = new Client();
        $this->client->setTransport($this->transport);
    }

    public function tearDown()
    {
        unset($this->client);
        unset($this->transport);
    }

    public function testDefaultTransportIsSocket()
    {
        $client = new Client();
        $this->assertTrue($client->getTransport() instanceof SocketTransport);
    }

    public function testSetGetTransport()
    {
        $this->client->setTransport($this->transport);
        $this->assertSame($this->transport, $this->client->getTransport());
    }

    public function testRedirectCountIsZero()
    {
        $this->assertEquals(0, $this->client->getRedirectionsCount());
    }

    public function testRedirectionCountIncremented()
    {
        $respQueue = $this->transport->getResponseQueue();
        $respQueue->enqueue(Response::fromString("HTTP/1.1 301 Moved Permanently\r\nLocation: /otherUrl\r\n\r\n"));
        $respQueue->enqueue(Response::fromString("HTTP/1.1 301 Moved Permanently\r\nLocation: /oneMoreLocation\r\n\r\n"));

        $request = new Request();
        $request->setUri("http://www.example.com/");
        $resp = $this->client->send($request);

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(2, $this->client->getRedirectionsCount());
    }

    public function testRedirectionLimit()
    {
        $this->client->setOptions(new ClientOptions(array('maxredirects' => 2)));

        $respQueue = $this->transport->getResponseQueue();
        $respQueue->enqueue(Response::fromString("HTTP/1.1 301 Moved Permanently\r\nLocation: /redirect1\r\n\r\n"));
        $respQueue->enqueue(Response::fromString("HTTP/1.1 301 Moved Permanently\r\nLocation: /redirect2\r\n\r\n"));
        $respQueue->enqueue(Response::fromString("HTTP/1.1 301 Moved Permanently\r\nLocation: /redirect3\r\n\r\n"));

        $request = new Request();
        $request->setUri("http://www.example.com/");
        $resp = $this->client->send($request);

        $this->assertEquals(301, $resp->getStatusCode());
        $this->assertEquals("/redirect3", $resp->getHeaders()->get('Location')->getFieldValue());
    }

    public function testSettingGlobalHeader()
    {
        $uaString = "MyHttpClient/1.1";
        $this->client->getHeaders()->addHeaderLine("User-agent: $uaString");

        $request = new Request();
        $request->setUri("http://www.example.com/");
        $this->assertFalse($request->getHeaders()->has('User-agent'));

        $resp = $this->client->send($request);

        $this->assertEquals($uaString, $request->getHeaders()->get('User-agent')->getFieldValue());
    }

    public function testSettingGlobalHeaderDoesntOverrideLocalHeader()
    {
        $uaString = "OtherHttpClient/1.0";
        $this->client->getHeaders()->addHeaderLine("User-agent: MyHttpClient/1.1");

        $request = Request::fromString("GET / HTTP/1.1\r\nUser-agent: $uaString\r\n\r\n");
        $request->setUri('http://www.example.com/');
        $this->assertTrue($request->getHeaders()->has('User-agent'));

        $resp = $this->client->send($request);

        $this->assertEquals($uaString, $request->getHeaders()->get('User-agent')->getFieldValue());
    }

    public function testSetGetOptions()
    {
        $options = new ClientOptions();
        $this->client->setOptions($options);
        $this->assertSame($options, $this->client->getOptions());
    }

    public function testSetOptionPassesTransportOptions()
    {
        $options = new ClientOptions();
        $transportOptions = new TransportOptions(array('sslVerifyPeer' => false));
        $options->setTransportOptions($transportOptions);

        $this->client->setOptions($options);
        $this->assertSame($transportOptions, $this->transport->getOptions());
        $this->assertFalse($this->transport->getOptions()->getSslVerifyPeer());
    }

    public function testSetOptionPassesTransportOptionsAsArray()
    {
        $options = new ClientOptions(array(
            'transportOptions' => new TransportOptions(array('sslVerifyPeer' => false))
        ));

        $this->client->setOptions($options);

        $this->assertFalse($this->transport->getOptions()->getSslVerifyPeer());
    }

    public function testSetOptionsPassesTransportOptionsImplicitlyAtSend()
    {
        $client = new MockClient();
        $options = new ClientOptions(array(
            'transportOptions' => new TransportOptions(array('sslVerifyPeer' => false))
        ));

        $client->setOptions($options);
        $client->getTransport()->getResponseQueue()->enqueue(
            Response::fromString("HTTP/1.1 200 Ok\r\nContent-length: 0\r\n\r\n")
        );

        $request = Request::fromString("GET / HTTP/1.1\r\nUser-agent: foobarclient\r\n\r\n");
        $request->setUri('http://www.example.com/');
        $response = $client->send($request);

        $this->assertFalse($client->getTransport()->getOptions()->getSslVerifyPeer());
    }
}

class MockClient extends Client
{
    protected static $defaultTransport = 'ZHttpClient2\Transport\Test';
}
