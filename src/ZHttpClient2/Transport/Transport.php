<?php

namespace ZHttpClient2\Transport;

use ZHttpClient2\Request;
use ZHttpClient2\Response;

interface Transport
{
    /**
     * Send HTTP request
     *
     * You can optionally provide a response object to be populated. If none
     * provided, the transport will create a default response object and return
     * it
     *
     * @param  ZHttpClient2\Request  $request
     * @param  ZHttpClient2\Reponse  $response
     * @return ZHttpClient2\Response
     */
    public function send(Request $request, Response $response = null);

    /**
     * Set configuration of transport adapter
     *
     * @param  ZHttpClient2\Transport\Options   $options
     * @return ZHttpClient2\Transport\Transport
     */
    public function setOptions(Options $options);

    /**
     * Get configuration of transport adapter
     *
     * @return ZHttpClient2\Transport\Options
     */
    public function getOptions();
}
