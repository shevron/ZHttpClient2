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

        // Check that values for challange parameters were set

        $response = $this->calculateChallangeResponse($request);

        $headerParams = array(
            'username' => $this->username,
            'realm'    => $this->realm,
            'nonce'    => $this->nonce,
            'uri'      => $request->getUri(),
            'qop'      => $this->qop,
        );

        if ($this->qop) {
            $headerParams['cnonce'] = $this->cnonce;
            $headerParams['nc'] = sprintf("%08x", $this->nc);
        }

        $header = "Digest ";
        foreach($headerParams as $key => $value) {
            $header .= "$key=\"$value\", ";
        }

        $header .= "response=\"$response\"";

        $request->getHeaders()->addHeaderLine("Authorization", $header);
    }

    /**
     * Calculate the response to the authentication challange
     *
     * @param  Request $request
     * @return string
     */
    protected function calculateChallangeResponse(Request $request)
    {
        $ha1 = md5($this->username . ':' . $this->realm . ':' . $this->password);
        if (empty($this->qop) || strtolower($this->qop) == 'auth') {
            $ha2 = md5($request->getMethod() . ':' . $request->getUri()->getPath());
        } elseif (strtolower($this->qop) == 'auth-int') {
            $ha2 = md5($this->getMethod() . ':' . $this->getUri()->getPath() . ':' . md5($request->getContent()));
        }

        if (empty($this->qop)) {
            $response = md5($ha1 . ':' . $this->nonce . ':' . $ha2);
        } else {
            $response = md5($ha1 . ':' . $this->nonce . ':' . $this->nc . ':' .
                    $this->cnonce . ':' . $this->qoc . ':' . $ha2);
        }

        return $response;
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
        $headerValue = $header->getFieldValue();

        if (strtolower(substr($headerValue, 0, 7)) != 'Digest ') {
            return false; // Not a Digest WWW-Authenticate header
        }

        $headerValue = trim(substr($headerValue, 6));

        $authParams = explode(',', $headerValue);

        return array();
    }
}