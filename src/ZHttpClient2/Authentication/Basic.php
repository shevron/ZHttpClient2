<?php

namespace ZHttpClient2\Authentication;

use ZHttpClient2\Request;

class Basic implements AuthenticationProviderInterface
{
    /**
     * The user's username
     *
     * @var username
     */
    protected $username;

    /**
     * The user's password
     *
     * @var string
     */
    protected $password;

    /**
     * Create a new Basic authentication object
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Authentiate the request
     *
     * @see \ZHttpClient2\Authentication\AuthenticationProviderInterface::authenticate()
     */
    public function authenticate(Request $request)
    {
        $authData = base64_encode("{$this->username}:{$this->password}");
        $request->getHeaders()->addHeaderLine('Authorization', "Basic $authData");
    }
}