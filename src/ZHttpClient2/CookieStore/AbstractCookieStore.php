<?php

namespace ZHttpClient2\CookieStore;

use Zend\Http\Header\Exception\InvalidArgumentException;
use Zend\Http\Header\SetCookie as SetCookieHeader;
use Zend\Http\Header\Cookie as CookieHeader;
use Zend\Http\Header\SetCookie;
use Zend\Uri\Http as HttpUri;
use ZHttpClient2\Request;
use ZHttpClient2\Response;

abstract class AbstractCookieStore implements \IteratorAggregate
{
    /**
     * Add a cookie to the storage from a Set-Cookie header
     *
     * @param  \Zend\Http\Header\SetCookie                $header
     * @return \ZHttpClient2\CookieStore\AbstractCookieStore
     */
    public function addCookieFromHeader(SetCookieHeader $header, HttpUri $defaultUri = null)
    {
        if (! $defaultUri) {
            $defaultUri = new HttpUri();
        }

        $this->addCookie(
            $header->getName(),
            $header->getValue(),
            $header->getDomain() ?: $defaultUri->getHost(),
            $header->getExpires(true),
            $header->getPath() ?: $defaultUri->getPath(),
            $header->isSecure(),
            $header->isHttponly()
        );

        return $this;
    }

    /**
     * Read all cookies from an HTTP response
     *
     * @param  \ZHttpClient2\Response $response
     * @param  \Zend\Uri\Http         $uri      HTTP URI to get defaults from
     * @return \ZHttpClient2\CookieStore\AbstractCookieStore
     */
    public function readCookiesFromResponse(Response $response, HttpUri $uri)
    {
        $cookies = null;
        try {
            $cookies = $response->getHeaders()->get('Set-Cookie');
        } catch (InvalidArgumentException $ex) {
            // Silently drop headers
        }

        if ($cookies) {
            foreach ($cookies as $cookieHeader) {
                $this->addCookieFromHeader($cookieHeader, $uri);
            }
        }

        return $this;
    }

    /**
     * Get the 'Cookie:' header object containing all cookies matched for
     * a specific request
     *
     * @param  \ZHttpClient2\Request       $request
     * @return \Zend\Http\Header\Cookie
     */
    public function getCookiesForRequest(Request $request)
    {
        $cookies = $this->getMatchingCookies($request->getUri());
        $nvPairs = array();
        foreach($cookies as $setCookie) { /* @var $setCookie SetCookie */
            $nvPairs[$setCookie->getName()] = $setCookie->getValue();
        }

        return new CookieHeader($nvPairs);
    }

    abstract public function addCookie($name, $value, $domain, $expires = null, $path = null, $secure = false, $httpOnly = true);

    /**
     * Get matching cookies for a URL
     *
     * @param Zend\Uri\Http|string $url
     * @param boolean              $includeSessionCookies
     * @param integer              $now
     */
    abstract public function getMatchingCookies($url, $includeSessionCookies = true, $now = null);
}
