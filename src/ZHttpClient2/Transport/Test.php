<?php

namespace ZHttpClient2\Transport;

use ZHttpClient2\Request;
use ZHttpClient2\Response;

class Test implements Transport
{
    /**
     * Request queue - if set, will be populated with request objects sent out
     *
     *  @var \SplQueue
     */
    protected $requestQueue;

    /**
     * Response queue
     *
     * @var \SplQueue
     */
    protected $responseQueue;

    /**
     * The default HTTP response returned if there are no responses in the queue
     *
     * @var Zend\Http\Response
     */
    protected $defaultResponse;

    /**
     * Options object
     *
     * @var Zend\Http\Transport\Options
     */
    protected $options = null;

    /**
     * Send request
     *
     * This will return the pre-defined response object
     *
     * @see Zend\Http\Transport\Transport::send()
     */
    public function send(Request $request, Response $response = null)
    {
        if ($this->requestQueue instanceof \SplQueue) {
            $this->requestQueue->enqueue($request);
        }

        if (count($this->getResponseQueue())) {
            return $this->getResponseQueue()->dequeue();
        } elseif ($this->defaultResponse) {
            return $this->defaultResponse;
        } elseif ($response) {
            return $response;
        } else {
            throw new Exception\ConfigurationException("Response queue is empty and no default response has been defined");
        }
    }

    /**
     * Set configuration of transport adapter
     *
     * @param  Zend\Http\Transport\Options   $options
     * @return Zend\Http\Transport\Transport
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set the request queue. If nothing is passed, a new queue will be created.
     *
     * If set, the request queue will store all requests passing through the
     * transport adapter.
     *
     * @param  \SplQueue                 $queue
     * @return \Zend\Http\Transport\Test
     */
    public function setRequestQueue(\SplQueue $queue = null)
    {
        if ($queue === null) {
            $queue = new \SplQueue();
        }

        $this->requestQueue = $queue;

        return $this;
    }

    /**
     * Set the response queue object
     *
     * @param  \SplQueue                 $queue
     * @return \Zend\Http\Transport\Test
     */
    public function setResponseQueue(\SplQueue $queue)
    {
        $this->responseQueue = $queue;

        return $this;
    }

    /**
     * Set the default response object.
     *
     * This object will be returned when there are no responses in the queue
     *
     * @param Zend\Http\Response $response
     */
    public function setDefaultResponse(Response $response)
    {
        $this->defaultResponse = $response;
    }

    /**
     * Get options for the test transport object
     *
     * @return Zend\Http\Transport\Options
     * @see    Zend\Http\Transport\Transport::getOptions()
     */
    public function getOptions()
    {
        if (! $this->options) {
            $this->options = new Options();
        }

        return $this->options;
    }

    /**
     * Get the default response object, or null if none was set
     *
     * @return Zend\Http\Response
     */
    public function getDefaultResponse()
    {
        return $this->defaultResponse;
    }

    /**
     * Get the request queue, if set
     *
     * @return \SplQueue
     */
    public function getRequestQueue()
    {
        return $this->requestQueue;
    }

    /**
     * Get the response queue object
     *
     * @return \SplQueue
     */
    public function getResponseQueue()
    {
        if (! $this->responseQueue) {
            $this->responseQueue = new \SplQueue();
        }

        return $this->responseQueue;
    }
}
