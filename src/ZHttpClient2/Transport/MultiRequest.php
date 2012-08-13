<?php

namespace ZHttpClient2\Transport;

use ZHttpClient2\RequestPool;

interface MultiRequest extends Transport
{
    /**
     * Send a pool of HTTP requests concurrently
     *
     * Returns an array of responses corresponding to the provided pool of
     * requests. If provided, $responseClass will be used as the class for each
     * response object. Otherwise, the default response class will be used.
     *
     * @param  ZHttpClient2\RequestPool    $request
     * @param  ZHttpClient2\Reponse|string $responseClass
     * @return array
     */
    public function sendMulti(RequestPool $requestPool);
}
