<?php

namespace ZHttpClient2;

use Zend\Stdlib\AbstractOptions;
use ZHttpClient2\Transport\Options as TransportOptions;

class ClientOptions extends AbstractOptions
{
    /**
     * Maximal number of HTTP redirects to follow for a single request
     *
     * @var integer
     */
    protected $maxRedirects = 5;

    /**
     * Whether to enable strict redirections.
     *
     * In strict redirections mode, the HTTP client will send a POST request
     * again following a 301 or 302 response returned for a POST request.
     * Otherwise, HTTP GET will be sent regardless of the original request
     * method.
     *
     * @var boolean
     */
    protected $strictRedirects = false;

    /**
     * Default HTTP user agent string
     *
     * @var string
     */
    protected $userAgent = 'Zend\Http\Client';

    /**
     * Whether to encode cookies or not
     *
     * @var boolean
     */
    protected $encodeCookies = true;

    /**
     * Transport configuration object
     *
     * @var Zend\Http\Transport\TransportOptions
     */
    protected $transportOptions = null;

    /**
     * Get the number of max redirections allowed
     *
     * @return integer
     */
    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    /**
     * Get whether strict redirections are enabled or not
     *
     * @return boolean
     */
    public function getStrictRedirects()
    {
        return $this->strictRedirects;
    }

    /**
     * Get the default user-agent string
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Get whether to URL-encode cookies or not
     *
     * @return string
     */
    public function getEncodeCookies()
    {
        return $this->encodeCookies;
    }

    /**
     * Get the transport object configuration
     *
     * If none was set, will return a default transport configuration object
     *
     * @return Zend\Http\Transport\TransportOptions
     */
    public function getTransportOptions()
    {
        if (! $this->transportOptions instanceof TransportOptions) {
            $this->transportOptions = new TransportOptions();
        }

        return $this->transportOptions;
    }

    /**
     * Check whether transport options are included in this client options object
     *
     * @return boolean
     */
    public function hasTransportOptions()
    {
        return ($this->transportOptions instanceof TransportOptions);
    }

    /**
     * @param number $maxRedirects
     */
    public function setMaxRedirects ($maxRedirects)
    {
        $this->maxRedirects = (integer) $maxRedirects;

        return $this;
    }

    /**
     * @param boolean $strictRedirects
     */
    public function setStrictRedirects ($strictRedirects)
    {
        $this->strictRedirects = (boolean) $strictRedirects;

        return $this;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string) $userAgent;

        return $this;
    }

    /**
     * @param boolean $encodeCookies
     */
    public function setEncodeCookies($encodeCookies)
    {
        $this->encodeCookies = (boolean) $encodeCookies;

        return $this;
    }

    /**
     * @param \Zend\Http\Transport\TransportOptions $transportOptions
     */
    public function setTransportOptions(TransportOptions $transportOptions)
    {
        $this->transportOptions = $transportOptions;

        return $this;
    }

}
