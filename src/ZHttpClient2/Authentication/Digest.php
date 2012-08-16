<?php

namespace ZHttpClient2\Authentication;

use Zend\Http\Header\WWWAuthenticate;
use ZHttpClient2\Request;
use ZHttpClient2\Exception\InvalidArgumentException;

class Digest implements AuthenticationProviderInterface
{
    protected $username;

    protected $password;

    protected $realm;

    protected $qop;

    protected $nonce;

    protected $opaque;

    protected $nc;

    protected $cnonce;

    /**
     * Create a new Digest authentication provider
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
     * Set authentication challange - usually taken from the WWW-Authenticate
     * HTTP response header
     *
     * Can be provided as a WWWAuthenticate header object, or as an associative
     * array containing the following keys (some are optional):
     *
     *   realm, qop, nonce, opaque, nc, cnonce
     *
     * @param  Zend\Http\Header\WWWAuthenticate | array $challange
     * @throws InvalidArgumentException
     */
    public function setChallange($challange)
    {
        if ($challange instanceof WWWAuthenticate) {
            $challange = $this->parseAuthenticateHeader($challange);
        }

        if (! (is_array($challange) || $challange instanceof \ArrayAccess)) {
            throw new InvalidArgumentException("Challange is expected to be an array or a WWW-Authenticate header object");
        }
    }

    /**
     * Authenticate a request using Digest authentication
     *
     * @see \ZHttpClient2\Authentication\AuthenticationProviderInterface::authenticate()
     */
    public function authenticate(Request $request)
    {
        /* Copied from old Http\Client: */
        /* @todo: Implement */

        /*
        if (empty($digest)) {
            throw new Exception\InvalidArgumentException("The digest cannot be empty");
        }
        foreach ($digest as $key => $value) {
            if (!defined('self::DIGEST_' . strtoupper($key))) {
                throw new Exception\InvalidArgumentException("Invalid or not supported digest authentication parameter: '$key'");
            }
        }
        $ha1 = md5($user . ':' . $digest['realm'] . ':' . $password);
        if (empty($digest['qop']) || strtolower($digest['qop']) == 'auth') {
            $ha2 = md5($this->getMethod() . ':' . $this->getUri()->getPath());
        } elseif (strtolower($digest['qop']) == 'auth-int') {
            if (empty($entityBody)) {
                throw new Exception\InvalidArgumentException("I cannot use the auth-int digest authentication without the entity body");
            }
            $ha2 = md5($this->getMethod() . ':' . $this->getUri()->getPath() . ':' . md5($entityBody));
        }
        if (empty($digest['qop'])) {
            $response = md5($ha1 . ':' . $digest['nonce'] . ':' . $ha2);
        } else {
            $response = md5($ha1 . ':' . $digest['nonce'] . ':' . $digest['nc']
                    . ':' . $digest['cnonce'] . ':' . $digest['qoc'] . ':' . $ha2);
        }
        */
    }

    /**
     * Parse the WWW-Authenticate header object and return an associative array
     * of key-value elements.
     *
     * If header is not a Digest challange, will throw an exception
     *
     * @param  WWWAuthenticate $header
     * @return array
     */
    protected function parseAuthenticateHeader(WWWAuthenticate $header)
    {
        return array();
    }
}