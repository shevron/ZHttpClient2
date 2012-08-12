<?php

namespace ZHttpClient2\Transport;

use ZHttpClient2\Request;
use
    Zend\Http\Response;

interface Transport
{
    /**
     * Send HTTP request
     *
     * You can optionally provide a response object to be populated. If none
     * provided, the transport will create a default response object and return
     * it
     *
     * @param  Zend\Http\Request  $request
     * @param  Zend\Http\Reponse  $response
     * @return Zend\Http\Response
     */
    public function send(Request $request, Response $response = null);

    /**
     * Set configuration of transport adapter
     *
     * @param  Zend\Http\Transport\Options   $options
     * @return Zend\Http\Transport\Transport
     */
    public function setOptions(Options $options);

    /**
     * Get configuration of transport adapter
     *
     * @return Zend\Http\Transport\Options
     */
    public function getOptions();
}
