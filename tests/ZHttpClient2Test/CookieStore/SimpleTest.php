<?php

/**
 * @namespace
 */
namespace ZHttpClient2Test\Transport;

use ZHttpClient2\CookieStore\Simple as SimpleCookieStore;
use ZHttpClient2\Request;
use ZHttpClient2\Response;
use Zend\Http\Header\Cookie;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cookie store
     *
     * @var Zend\Http\CookieStore\Simple
     */
    protected $cs = null;

    public function setUp()
    {
        $this->cs = new SimpleCookieStore();
    }

    public function tearDown()
    {
        $this->cs = null;
    }

    public function testAddGetSingleCookie()
    {
        $this->cs->addCookie('foo', 'bar', 'example.com');
        $cookies = $this->cs->getMatchingCookies('http://example.com/');
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('foo=bar', Cookie::fromSetCookieArray($cookies)->getFieldValue());
    }

    public function testAddGetNonMatchingCookie()
    {
        $this->cs->addCookie('foo', 'bar', 'example.com');
        $cookies = $this->cs->getMatchingCookies('http://rexample.com/');
        $this->assertEquals(0, count($cookies));
    }

    public function testAddGetMultipleCookiesSubdomainMatch()
    {
        $this->cs->addCookie('foo1', 'bar1', 'example.com');
        $this->cs->addCookie('foo2', 'bar2', 'www.example.com');
        $this->cs->addCookie('foo3', 'bar3', 'other.example.com');
        $this->cs->addCookie('foo4', 'bar4', 'www.other.com');

        $cookies = $this->cs->getMatchingCookies('http://www.example.com/');

        $this->assertEquals(2, count($cookies));
        $this->assertEquals('foo1=bar1; foo2=bar2', Cookie::fromSetCookieArray($cookies)->getFieldValue());
    }

    public function testAddGetMultipleCookiesPathMatch()
    {
        $this->cs->addCookie('foo1', 'bar1', 'example.com');
        $this->cs->addCookie('foo2', 'bar2', 'example.com', null, '/foo');
        $this->cs->addCookie('foo3', 'bar3', 'example.com', null, '/foo/bar');
        $this->cs->addCookie('foo4', 'bar4', 'example.com', null, '/foo/bar/baz');
        $this->cs->addCookie('foo5', 'bar5', 'example.com', null, '/baz');

        $cookies = $this->cs->getMatchingCookies('http://example.com/foo/bar');

        $this->assertEquals(3, count($cookies));
        $this->assertEquals('foo1=bar1; foo2=bar2; foo3=bar3', Cookie::fromSetCookieArray($cookies)->getFieldValue());
    }

    public function testAddGetMultipleCookiesExpiry()
    {
        $this->cs->addCookie('foo1', 'bar1', 'example.com', date(DATE_RFC2822, $_SERVER['REQUEST_TIME'] - 10));
        $this->cs->addCookie('foo2', 'bar2', 'example.com', date(DATE_RFC2822, $_SERVER['REQUEST_TIME'] + 3600));
        $this->cs->addCookie('foo3', 'bar3', 'example.com', null);

        // Test with current time
        $cookies = $this->cs->getMatchingCookies('http://example.com/qwe');
        $this->assertEquals(2, count($cookies));
        $this->assertEquals('foo2=bar2; foo3=bar3', Cookie::fromSetCookieArray($cookies)->getFieldValue());

        // Test with past time
        $cookies = $this->cs->getMatchingCookies('http://example.com/qwe', true, $_SERVER['REQUEST_TIME'] - 3600);
        $this->assertEquals(3, count($cookies));
        $this->assertEquals('foo1=bar1; foo2=bar2; foo3=bar3', Cookie::fromSetCookieArray($cookies)->getFieldValue());

        // Test without session cookies
        $cookies = $this->cs->getMatchingCookies('http://example.com/qwe', false);
        $this->assertEquals(1, count($cookies));
        $this->assertEquals('foo2=bar2', Cookie::fromSetCookieArray($cookies)->getFieldValue());
    }

    public function testLoadCookiesFromResponse()
    {
        $year = date('Y') + 1;
        $response = Response::fromString(
            "HTTP/1.1 302 OK\r\n" .
            "Server: Apache\r\n" .
            "Set-Cookie: store=deleted; expires=Mon, 21-Feb-2011 20:35:03 GMT; path=/; domain=www.example.com; httponly, frontend=7e9ca2b0396144302299f0857a5d97ed; expires=Tue, 21-Feb-$year 21:35:04 GMT; path=/; domain=www.example.com; httponly\r\n" .
            "Set-Cookie: foo=bar; path=/; domain=example.com, foo2=bar2; path=/some/long/path; domain=example.com\r\n" .
            "Content-length: 0\r\n" .
            "\r\n"
        );

        $this->cs->readCookiesFromResponse($response);

        // Count total numner of cookies
        $i = 0;
        foreach ($this->cs as $c) { $i++; }
        $this->assertEquals(4, $i);

        return $this->cs;
    }

    /**
     * @depends testLoadCookiesFromResponse
     * @param Zend\Http\CookieStore\Simple $cs
     */
    public function testCookiesMatchRequest($cs)
    {
        $request = new Request();
        $request->setUri('http://www.example.com/qwe');
        $cookies = $cs->getCookiesForRequest($request);
        $this->assertEquals(2, count($cookies));
    }
}
