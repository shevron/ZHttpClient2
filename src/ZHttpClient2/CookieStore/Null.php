<?php

namespace ZHttpClient2\CookieStore;

use Zend\Http\Header\Cookie as CookieHeader;
use Zend\Http\Request;
use Zend\Http\Response;

class Null extends AbstractCookieStore
{
    public function addCookie($name, $value, $domain, $expires = null, $path = null, $secure = false, $httpOnly = true)
    {
        return $this;
    }

    public function getMatchingCookies($url, $includeSessionCookies = true, $now = null)
    {
        return array();
    }

    public function getIterator()
    {
        return new \EmptyIterator();
    }
}
