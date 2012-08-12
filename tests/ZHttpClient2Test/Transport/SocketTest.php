<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Transport;

use ZHttpClient2\Transport\Socket as SocketTransport;
use ZHttpClient2\Transport\SocketOptions;
use ZHttpClient2\Transport\Options as TransportOptions;
use ZHttpClient2\Request;

class SocketTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the correct request method is sent over the wire
     *
     * @param string $method
     * @dataProvider requestMethodProvider
     */
    public function testCorrectRequestMethod($method)
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/test');
        $request->setMethod($method);

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $this->assertRegExp("/^$method /", $requestStr);
    }

    /**
     * Test that the correct request URI is sent over the wire
     *
     * @param string $fullUri
     * @param string $expected
     * @dataProvider requestUriProvider
     */
    public function testCorrectRequestUri($fullUri, $expected)
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri($fullUri);

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $expected = preg_quote($expected, '/');
        $this->assertRegExp("/^GET $expected /", $requestStr);
    }

    public function testCorrectHttpVersion()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');
        $request->setVersion('1.0');

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $this->assertRegExp('/^GET \/ HTTP\/1.0\r\n/', $requestStr);
    }

    public function testCorrectHeader()
    {
        $headerLine = "X-Foo-Bar: bla bla; version=1.0";

        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');
        $request->getHeaders()->addHeaderLine($headerLine);

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $expected = preg_quote($headerLine, '/');
        $this->assertRegExp("/^$headerLine\r\n/m", $requestStr);
    }

    public function testNoBodyRequestEndsWithNlBr()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $this->assertRegExp("/\r\n\r\n$/", $requestStr);
    }

    public function testSimplePostRequestWithUrlencodedData()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/')
                ->setMethod('POST')
                ->getPost()->fromArray(array('foo' => 'bar'));

        $request->getHeaders()->addHeaderLine('Content-type', 'application/x-www-form-urlencoded')
                           ->addHeaderLine('Content-length', $request->getContent()->getLength());

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $this->assertRegExp("/\r\nfoo=bar$/", $requestStr);
    }

    public function testConnectionHeaderIsAddedKeepaliveOn()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $this->assertRegExp("/^Connection: keep-alive\r\n/m", $requestStr);
    }

    public function testConnectionHeaderIsAddedKeepaliveOff()
    {
        $transport = new MockSocketTransport(new SocketOptions(array('keepalive' => false)));

        $request = new Request();
        $request->setUri('http://localhost/');

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $this->assertRegExp("/^Connection: close\r\n/m", $requestStr);
    }

    /**
     * @dataProvider hostProvider
     */
    public function testHostHeaderIsAdded($url, $expected)
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri($url);

        $transport->setNextResponse($this->getSimpleResponseString());
        $transport->send($request);
        $requestStr = $transport->getLastRequest();

        $expected = preg_quote($expected, "/");
        $this->assertRegExp("/^Host: $expected\r\n/m", $requestStr);
    }

    public function testReadResponseContentLength()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');

        // Add a few bytes at the end of the pipe after the next response
        $response = $this->getSimpleResponseString() . "--more data--";
        $transport->setNextResponse($response);

        $response = $transport->send($request);

        $this->assertEquals('Hi!', $response->getBody());
    }

    public function testReadResponseChunkedEncoding()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');

        $transport->setNextResponse($this->getResponseFromFile('response-chunked-01.http'));

        $response = $transport->send($request);

        $this->assertEquals('25ca3d1bd09ae087507fb8cc71fa925b', md5($response->getBody()));
    }

    public function testReadResponseConnectionClosed()
    {
        $transport = new MockSocketTransport();

        $request = new Request();
        $request->setUri('http://localhost/');

        $transport->setNextResponse($this->getResponseFromFile('response-connectionclose-01.http'));

        $response = $transport->send($request);

        $this->assertEquals('25ca3d1bd09ae087507fb8cc71fa925b', md5($response->getBody()));
    }

    public function testSetGetSocketOptionsObject()
    {
        $options = new SocketOptions();
        $transport = new SocketTransport();
        $transport->setOptions($options);
        $this->assertSame($options, $transport->getOptions());
    }

    public function testSetGetTransportOptionsObject()
    {
        $options = new TransportOptions(array('timeout' => 5));
        $transport = new SocketTransport();
        $transport->setOptions($options);

        $this->assertTrue($transport->getOptions() instanceof SocketOptions);
        $this->assertEquals(5, $transport->getOptions()->getTimeout());
    }

    /**
     * Helper functions
     */

    protected function getSimpleResponseString()
    {
        return "HTTP/1.1 200 OK\r\n" .
               "Server: not-really-a-server/0.0\r\n" .
               "Date: " . date(DATE_RFC822) . "\r\n" .
               "Content-length: 3\r\n" .
               "Content-type: text/plain\r\n" .
               "\r\n" .
               "Hi!";
    }

    protected function getResponseFromFile($file)
    {
        return file_get_contents(__DIR__ . '/_files/' . $file);
    }

    /**
     * Data Providers
     */

    public static function requestMethodProvider()
    {
        return array(
            array('GET'),
            array('DELETE')
        );
    }

    public static function requestUriProvider()
    {
        return array(
            array('http://www.example.com/foo/bar', '/foo/bar'),
            array('http://www.example.com/foo/bar?q=foobar&p=grrr', '/foo/bar?q=foobar&p=grrr'),
            array('http://www.example.com/?q=foobar&p=grrr', '/?q=foobar&p=grrr'),
            array('http://www.example.com/foo/bar?q=foobar#fragment', '/foo/bar?q=foobar'),
            array('http://www.example.com', '/'),
        );
    }

    public static function hostProvider()
    {
        return array(
            array('http://www.example.com/', 'www.example.com'),
            array('http://www.example.com:80/', 'www.example.com'),
            array('http://www.example.com:82/', 'www.example.com:82'),
            array('https://www.example.com/', 'www.example.com'),
            array('https://www.example.com:443/', 'www.example.com'),
            array('https://www.example.com:80/', 'www.example.com:80'),
        );
    }
}

class MockSocketTransport extends SocketTransport
{
    protected $lastRequest = null;

    protected $nextResponse = null;

    /**
     * Mock the connection by opening a php://temp socket
     *
     * @param  Zend\Http\Request                $request
     * @throws Exception\ConfigurationException
     * @throws Exception\ConnectionException
     */
    protected function connect(Request $request)
    {
        $this->socket = fopen('php://temp', 'r+');
        $this->connectedTo = 'php://temp';
    }

    /**
     * Handle mock content before / after sending request
     *
     * @param  Zend\Http\Request             $request
     * @throws Exception\ConnectionException
     */
    protected function sendRequest(Request $request)
    {
        parent::sendRequest($request);

        // Save last request
        fseek($this->socket, 0);
        $this->lastRequest = stream_get_contents($this->socket);

        // Set next response
        $pos = ftell($this->socket);
        fwrite($this->socket, $this->nextResponse);
        fseek($this->socket, $pos);
    }

    /**
     * Get last request as string
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Set next response from string
     *
     * @param string $response
     */
    public function setNextResponse($response)
    {
        $this->nextResponse = $response;
    }

    /**
     * Reset the stream - close it an open a new temp stream
     *
     */
    public function resetStream()
    {
        fclose($this->socket);
        $this->socket = fopen('php://temp', 'r+');
    }
}
