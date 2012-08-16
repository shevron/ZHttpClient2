<?php

namespace ZHttpClient2\Authentication;

use ZHttpClient2\Request;

interface AuthenticationProviderInterface
{
    /**
     * Authenticate a request before it is sent
     *
     * @param Request $request
     */
    public function authenticate(Request $request);
}