<?php

namespace ZHttpClient2\Transport;

use Zend\Stdlib\AbstractOptions;

class Options extends AbstractOptions implements \IteratorAggregate
{
    /**
     * Whether to use keep-alive if server allows it
     *
     * HTTP Keep-alive allows sending multiple HTTP request on a single TCP
     * connection, thus improving efficiency of consecutive requests to the
     * same server.
     *
     * @var boolean
     */
    protected $keepAlive = true;

    /**
     * Timeout in seconds for connecting to and reading from the server
     *
     * @var integer
     */
    protected $timeout = 30;

    /**
     * Class to use for response objects
     *
     * @var string
     */
    protected $defaultResponseClass = '\ZHttpClient2\Response';

    /**
     * Class to use for response body objects
     *
     * This is only used if the response object does not contain a
     * pre-instantiated body object
     *
     * @var string
     */
    protected $defaultResponseBodyClass = '\ZHttpClient2\Entity\SmartBuffer';

    /**
     * SSL client cerfiticate file
     *
     * If your server requires a client certificate, this should point to a PEM
     * encoded certificate file
     *
     * @var string
     */
    protected $sslCertificate = null;

    /**
     * Passphrase for the SSL client certificate
     *
     * This is only used if $_sslCertificate is set, and if it is passphrase
     * protected.
     *
     * @var string
     */
    protected $sslPassphrase = null;

    /**
     * Whether or not to verify SSL peer
     *
     * For security reasons, SSL server certificate should be verified against
     * a CA on each connection, to ensure no MITM attacks are preformed. If
     * you are connecting to a server without a valid certificate or with a
     * self-signed certificate, and are sure you know what you are doing, you
     * can set this to 'false'.
     *
     * @var boolean
     */
    protected $sslVerifyPeer = true;

    /**
     * Path to the SSL certificate authority file
     *
     * Setting this allows you to control the SSL layer's known certificate
     * authorities, used to validate peers. Usually there is no need to modify
     * this, unless your OpelSSL environment is not properly configured or you
     * are using certificates signed by a custom certificate authority.
     *
     * @var string
     */
    protected $sslCaFile = null;

    /**
     * Path to the SSL certificate authority directory
     *
     * Similar to $sslCaFile but allows pointing a cerificate directory.
     * The format in which the directory is organized may be implementation
     * specific.
     *
     * @var string
     */
    protected $sslCaPath = null;

    /**
     * @return the $keepAlive
     */
    public function getKeepAlive()
    {
        return $this->keepAlive;
    }

    /**
     * @return the $timeout
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return the $defaultResponseClass
     */
    public function getDefaultResponseClass()
    {
        return $this->defaultResponseClass;
    }

    /**
     * @return the $defaultResponseBodyClass
     */
    public function getDefaultResponseBodyClass()
    {
        return $this->defaultResponseBodyClass;
    }

    /**
     * @return the $sslCertificate
     */
    public function getSslCertificate()
    {
        return $this->sslCertificate;
    }

    /**
     * @return the $sslPassphrase
     */
    public function getSslPassphrase()
    {
        return $this->sslPassphrase;
    }

    /**
     * @return the $sslVerifyPeer
     */
    public function getSslVerifyPeer()
    {
        return $this->sslVerifyPeer;
    }

    /**
     * @return the $sslCaFile
     */
    public function getSslCaFile()
    {
        return $this->sslCaFile;
    }

    /**
     * @return the $sslCaPath
     */
    public function getSslCaPath()
    {
        return $this->sslCaPath;
    }

    /**
     * @param boolean $keepAlive
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = $keepAlive;

        return $this;
    }

    /**
     * @param number $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param string $defaultResponseClass
     */
    public function setDefaultResponseClass($defaultResponseClass)
    {
        if (! ($defaultResponseClass == '\ZHttpClient2\Response' ||
               is_subclass_of('\ZHttpClient2\Response', $defaultResponseClass))) {

            throw new Exception\InvalidArgumentException('Invalid argument passed as default response class: not a subclass of ZHttpClient2\Response');
        }

        $this->defaultResponseClass = $defaultResponseClass;

        return $this;
    }

    /**
     * @param string $defaultResponseBodyClass
     */
    public function setDefaultResponseBodyClass($defaultResponseBodyClass)
    {
        $this->defaultResponseBodyClass = $defaultResponseBodyClass;

        return $this;
    }

    /**
     * @param string $sslCertificate
     */
    public function setSslCertificate($sslCertificate)
    {
        $this->sslCertificate = $sslCertificate;

        return $this;
    }

    /**
     * @param string $sslPassphrase
     */
    public function setSslPassphrase($sslPassphrase)
    {
        $this->sslPassphrase = $sslPassphrase;

        return $this;
    }

    /**
     * @param boolean $sslVerifyPeer
     */
    public function setSslVerifyPeer($sslVerifyPeer)
    {
        $this->sslVerifyPeer = $sslVerifyPeer;

        return $this;
    }

    /**
     * @param string $sslCaFile
     */
    public function setSslCaFile($sslCaFile)
    {
        $this->sslCaFile = $sslCaFile;

        return $this;
    }

    /**
     * @param string $sslCaPath
     */
    public function setSslCaPath($sslCaPath)
    {
        $this->sslCaPath = $sslCaPath;

        return $this;
    }

    /**
     * Get iterator object - defined by the IteratorAggregate interface
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $vars = get_object_vars($this);
        foreach($vars as $k => $v) {
            if (substr($k, 0, 2) == '__') unset($vars[$k]);
        }

        return new \ArrayIterator($vars);
    }
}
