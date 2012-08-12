<?php

namespace ZHttpClient2\CookieStore;

use ArrayObject;
use Zend\Uri\Http as HttpUri;
use ZHttpClient2\Header\SetCookie as SetCookieHeader;

class Simple extends AbstractCookieStore
{
    protected $cookies = array();

    protected $cookieRefs = array();

    public function addCookie($name, $value, $domain, $expires = null, $path = null, $secure = false, $httpOnly = true)
    {
        $cookie = new SetCookieHeader($name, $value, $domain, $expires, $path, $secure, $httpOnly);
        $this->addCookieFromHeader($cookie);

        return $this;
    }

    /**
     * Store a cookie header in the storage
     *
     * This overrides the parent method, as in the case of the Simple storage
     * we actually store all cookies as a SetCookie header object - and there
     * is no need to convert the data into anything else.
     *
     * @see Zend\Http\CookieStore\AbstractCookieStore::addCookieFromHeader()
     */
    public function addCookieFromHeader(SetCookieHeader $header, HttpUri $defaultUri = null)
    {
        if (! $defaultUri) {
            $defaultUri = new HttpUri('/');
        }

        $cookieId = $this->getCookieId($header);
        $this->cookies[$cookieId] = $header;

        $cookieDomain = $header->getDomain() ?: $defaultUri->getHost();
        if (! isset($this->cookieRefs[$cookieDomain])) {
            $this->cookieRefs[$cookieDomain] = new ArrayObject();
        }

        $domain = $this->cookieRefs[$cookieDomain]; /* @var $domain \ArrayObject */

        $path = $header->getPath() ?: $defaultUri->getPath();

        if (! isset($domain[$path])) {
            $domain[$path] = new ArrayObject();
        }
        $cookieRefs = $domain[$path];
        $cookieRefs[$header->getName()] = $cookieId;

        return $this;
    }

    /**
     * Get an array of cookies matching a URL and some conditions
     *
     * @see Zend\Http\CookieStore.AbstractCookieStore::getMatchingCookies()
     */
    public function getMatchingCookies($url, $includeSessionCookies = true, $now = null)
    {
        if (is_string($url)) {
            $url = new HttpUri($url);
        }

        if (! $url instanceof HttpUri) {
            throw new Exception\InvalidArgumentException("\$url is expected to be a URL string or a Zend\\Uri\\Http object");
        }

        if (! ($url->isAbsolute() && $url->isValid())) {
            throw new Exception\InvalidArgumentException("Provided URL is not an absolute, valid HTTP URL: $url");
        }

        $url->normalize();

        // Find matching cookies
        $cookies = array();
        foreach ($this->cookieRefs as $domain => $paths) {
            if ($domain == $url->getHost() || preg_match('/\.' . preg_quote($domain) . '$/', $url->getHost())) {
                foreach ($paths as $path => $cookieRefs) {
                    if ($path == '/') $path = '';
                    if ($path == $url->getPath() || strpos($url->getPath(), $path . '/') === 0) {
                        // We have a possible match!
                        foreach ($cookieRefs as $cookieId) {
                            $cookie = $this->cookies[$cookieId]; /* @var $cookie \Zend\Http\Header\SetCookie */
                            if ($cookie->isExpired($now)) continue;
                            if ((! $includeSessionCookies) && $cookie->isSessionCookie()) continue;
                            if ($cookie->isSecure() && $url->getScheme() == 'http') continue;

                            $cookies[] = $cookie;
                        }
                    }
                }
            }
        }

        return $cookies;
    }

    /**
     * Get a unqiue ID for this cookie
     *
     * @param  Zend\Http\Header\SetCookie $header
     * @return string
     */
    protected function getCookieId(SetCookieHeader $header)
    {
        return $header->getName() . "," . $header->getDomain() . "," . $header->getPath();
    }

    /**
     * Get iterator object
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cookies);
    }
}
