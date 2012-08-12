<?php

namespace ZHttpClient2\CookieStore;

use ZHttpClient2\Header\SetCookie as SetCookieHeader;
use
    Zend\Http\Header\Cookie as CookieHeader,
    Zend\Http\Request,
    Zend\Http\Response,
    Zend\Uri\Http as HttpUri;

abstract class AbstractCookieStore implements \IteratorAggregate
{
    /**
     * Add a cookie to the storage from a Set-Cookie header
     *
     * @param  \Zend\Http\Header\SetCookie                $header
     * @return \Zend\Http\CookieStore\AbstractCookieStore
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
     * @param  \Zend\Http\Response                        $response
     * @param  \Zend\Uri\Http                             $uri      HTTP URI to get defaults from
     * @return \Zend\Http\CookieStore\AbstractCookieStore
     */
    public function readCookiesFromResponse(Response $response, HttpUri $uri)
    {
        $cookies = $response->getHeaders()->get('Set-Cookie');
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
     * @param  \Zend\Http\Request       $request
     * @return \Zend\Http\Header\Cookie
     */
    public function getCookiesForRequest(Request $request)
    {
        $cookies = $this->getMatchingCookies($request->getUri());

        return CookieHeader::fromSetCookieArray($cookies);
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
