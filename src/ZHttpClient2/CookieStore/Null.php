<?php

namespace ZHttpClient2\CookieStore;

use ZHttpClient2\Header\Cookie as CookieHeader;
use ZHttpClient2\Request;
use ZHttpClient2\Response;

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
