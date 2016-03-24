<?php

namespace ZHttpClient2\Transport;

class SocketOptions extends Options
{
    /**
     * SSL cryptography type
     *
     * Should be one of the STREAM_CRYPTO_METHOD_*_CLIENT constants defined by
     * PHP. This can be used to enforce TLS or SSLv3, for example.
     *
     * @var integer
     */
    protected $sslCryptoType = STREAM_CRYPTO_METHOD_ANY_CLIENT;

    /**
     * @return the $sslCryptoType
     */
    public function getSslCryptoType()
    {
        return $this->sslCryptoType;
    }

    /**
     * @param number $sslCryptoType
     */
    public function setSslCryptoType($sslCryptoType)
    {
        $this->sslCryptoType = $sslCryptoType;

        return $this;
    }
}
