<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Http
 */

namespace ZHttpClient2;

use Zend\Uri\Http as HttpUri;
use Zend\Http\Headers;
use Zend\Http\Header\Cookie;
use ZHttpClient2\CookieStore\AbstractCookieStore;
use ZHttpClient2\Transport\Transport as HttpTransport;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

/**
 * Http client
 *
 * @category   Zend
 * @package    ZHttpClient2
 */
class Client implements DispatchableInterface
{
    /**
     * Default transport adapter class
     *
     * @var string
     */
    protected static $defaultTransport = 'ZHttpClient2\Transport\Socket';

    /**
     * Default cookie storage container class
     *
     * @var string
     */
    protected static $defaultCookieStore = 'ZHttpClient2\CookieStore\Simple';

    /**
     * @var Transport
     */
    protected $transport;

    /**
     * @var array
     */
    protected $auth = array();

    /**
     * Cookie storage object
     *
     * @var ZHttpClient2\CookieStore\AbstractCookieStore
     */
    protected $cookieStore = null;

    /**
     * Global headers
     *
     * Global headers are headers that are set on all requests which are sent
     * by the client. These could include, for example, the 'User-agent' header
     *
     * @var Headers
     */
    protected $headers = null;

    /**
     * @var int
     */
    protected $redirectCounter = 0;

    /**
     * Options object
     *
     * @var ZHttpClient2\ClientOptions
     */
    protected $options = null;

    /**
     * Constructor
     *
     * @param string                  $uri
     * @param ZHttpClient2\ClientOptions $options
     */
    public function __construct(ClientOptions $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        } else {
            $this->options = new ClientOptions();
        }
    }

    /**
     * Set configuration options for this HTTP client
     *
     * @param  ZHttpClient2\ClientOptions $options
     * @return ZHttpClient2\Client
     * @throws Client\Exception
     */
    public function setOptions(ClientOptions $options)
    {
        $this->options = $options;

        // Pass configuration options to the adapter if it exists
        if ($this->transport instanceof HttpTransport && $options->hasTransportOptions()) {
            $this->transport->setOptions($options->getTransportOptions());
        }

        return $this;
    }

    /**
     * Get options object
     *
     * @return \ZHttpClient2\ClientOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transport adapter
     *
     * @param  \ZHttpClient2\Transport\Transport|string transportort
     * @return \ZHttpClient2\Client
     * @throws \ZHttpClient2\Client\Exception
     */
    public function setTransport(HttpTransport $transport)
    {
        $this->transport = $transport;

        if ($this->options->hasTransportOptions()) {
            $this->transport->setOptions($this->options->getTransportOptions());
        }

        return $this;
    }

    /**
     * Get the transport adapter
     *
     * @return \ZHttpClient2\Transport\Transport
     */
    public function getTransport()
    {
        if (! $this->transport) {
            $this->transport = new static::$defaultTransport();
            $this->transport->setOptions($this->options->getTransportOptions());
        }

        return $this->transport;
    }

    /**
     * Get the redirections count
     *
     * @return integer
     */
    public function getRedirectionsCount()
    {
        return $this->redirectCounter;
    }

    /**
     * Return the current cookie storage object
     *
     * @return ZHttpClient2\CookieStore\AbstractCookieStore
     */
    public function getCookieStore()
    {
        if (! $this->cookieStore) {
            $this->cookieStore = new static::$defaultCookieStore;
        }

        return $this->cookieStore;
    }

    /**
     * Set an array of cookies
     *
     * @param  ZHttpClient2\CookieStore\AbstractCookieStore $cookieStore
     * @return ZHttpClient2\Client
     */
    public function setCookieStore(AbstractCookieStore $cookieStore)
    {
        $this->cookieStore = $cookieStore;

        return $this;
    }

    /**
     * Get globl headers container
     *
     * @return \Zend\Http\Headers
     */
    public function getHeaders()
    {
        if (! $this->headers) {
            $this->headers = new Headers();
        }

        return $this->headers;
    }

    /**
     * Set the global headers container
     *
     * @param  \Zend\Http\Headers $headers
     * @return \ZHttpClient2\Client
     */
    public function setHeaders($headers)
    {
        if (is_array($headers)) {
            $newHeaders = new Headers();
            $newHeaders->addHeaders($headers);
            $headers = $newHeaders;
        }

        if (! $headers instanceof Headers) {
            throw new Exception\InvalidArgumentException("Headers should be either an array or a Headers object");
        }

        $this->headers = $headers;

        return $this;
    }

    /**
     * Create a HTTP authentication "Authorization:" header according to the
     * specified user, password and authentication method.
     *
     * @param  string $user
     * @param  string $password
     * @param  string $type
     * @return Client
     */
    public function setAuth($user, $password, $type = self::AUTH_BASIC)
    {
        if (!defined('self::AUTH_' . strtoupper($type))) {
            throw new Exception\InvalidArgumentException("Invalid or not supported authentication type: '$type'");
        }
        if (empty($user) || empty($password)) {
            throw new Exception\InvalidArgumentException("The username and the password cannot be empty");
        }

        $this->auth = array (
            'user'     => $user,
            'password' => $password,
            'type'     => $type

        );

        return $this;
    }

    /**
     * Calculate the response value according to the HTTP authentication type
     *
     * @see http://www.faqs.org/rfcs/rfc2617.html
     * @param  string         $user
     * @param  string         $password
     * @param  string         $type
     * @param  array          $digest
     * @return string|boolean
     */
    protected function calcAuthDigest($user, $password, $type = self::AUTH_BASIC, $digest = array(), $entityBody = null)
    {
        if (!defined('self::AUTH_' . strtoupper($type))) {
            throw new Exception\InvalidArgumentException("Invalid or not supported authentication type: '$type'");
        }
        $response = false;
        switch (strtolower($type)) {
            case self::AUTH_BASIC :
                // In basic authentication, the user name cannot contain ":"
                if (strpos($user, ':') !== false) {
                    throw new Exception\InvalidArgumentException("The user name cannot contain ':' in Basic HTTP authentication");
                }
                $response = base64_encode($user . ':' . $password);
                break;
            case self::AUTH_DIGEST :

                break;
        }

        return $response;
    }

    /**
     * Dispatch
     *
     * @param  \Zend\Stdlib\RequestInterface  $request
     * @param  \Zend\Stdlib\ResponseInterface $response
     * @return ResponseDescription
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        $response = $this->send($request);

        return $response;
    }

    /**
     * Send HTTP request and return a response
     *
     * @param  Request  $request
     * @return Response
     */
    public function send(Request $request, Response $response = null)
    {
        $this->redirectCounter = 0;
        $transport = $this->getTransport();

        while (true) {
            $this->prepareRequest($request);
            $response = $transport->send($request, $response);
            $this->handleResponse($response, $request);

            // If we got redirected, look for the Location header
            if ($response->isRedirect() &&
                $response->getHeaders()->has('Location') &&
                $this->redirectCounter < $this->options->getMaxRedirects()) {

                $request = $this->getNextRequestForRedirection($response, $request);

                ++$this->redirectCounter;

            } else {
                // Not a redirection, no location or redirect limit reached
                break;
            }
        }

        return $response;
    }

    public function getNextRequestForRedirection(Response $response, Request $request = null)
    {
        // Avoid problems with buggy servers that add whitespace at the
        // end of some headers
        $location = trim($response->getHeaders()->get('Location')->getFieldValue());

        // Check whether we send the exact same request again, or drop the parameters
        // and send a GET request
        if ($response->getStatusCode() == 303 ||
            (! $this->options->getStrictRedirects() &&
             ($response->getStatusCode() == 302 || $response->getStatusCode() == 301))) {

            $request->setMethod(Request::METHOD_GET);
            $request->setContent(null);
        }

        $uri = HttpUri::merge($request->getUri(), $location)->normalize();
        $request->setUri($uri);

        $headers = $request->getHeaders();
        if ($headers->has('Host')) {
            $headers->removeHeaders($headers->get('Host'));
        }

        return $request;
    }

    protected function prepareRequest(Request $request)
    {
        foreach ($this->getHeaders() as $header) {
            $key = $header->getFieldName();
            if (! $request->getHeaders()->has($key)) {
                $request->getHeaders()->addHeader($header);
            }
        }

        $existingCookies = $request->getCookie(); /* @var Zend\Http\Header\Cookie */
        $cookieHeader = $this->getCookieStore()->getCookiesForRequest($request);

        if ($existingCookies) {
            $request->getHeaders()->removeHeader($existingCookies);
            foreach ($existingCookies as $key => $value) {
                $cookieHeader[$key] = $value;
            }
        }

        if (count($cookieHeader)) {
            $request->getHeaders()->addHeader($cookieHeader);
        }

        // Handle POST content
        if ($request->getContent() instanceof Entity\FormDataHandler) {
            $request->getContent()->prepareRequestHeaders($request->getHeaders());
        }
    }

    /**
     * Handle an incoming response
     *
     * Can perform some tasks on incoming responses, such as read and store set
     * cookies
     *
     * @param ZHttpClient2\Response $response The response to handle
     * @param ZHttpClient2\Request  $request  The request that triggered the response
     *
     */
    protected function handleResponse(Response $response, Request $request)
    {
        $this->getCookieStore()->readCookiesFromResponse($response, $request->getUri());
    }

    /**
     * HTTP DSL methods
     */

    /**
     * GET a URL
     *
     * This is a convenience method for quickly sending a GET request to the
     * provided URL, without manually creating a request object
     *
     * @param  Zend\Uri\Http|string $url
     * @return ZHttpClient2\Response
     */
    public function get($url)
    {
        $request = new Request($url);
        $request->setMethod(Request::METHOD_GET);

        return $this->send($request);
    }

    /**
     * Send an HTTP POST request to the specified URL with an optional payload
     *
     * @param  Zend\Uri\Http|string $url
     * @param  mixed                $content
     * @param  string               $contentType
     * @return ZHttpClient2\Response
     */
    public function post($url, $content = null, $contentType = null)
    {
        $request = new Request($url);
        $request->setMethod(Request::METHOD_POST);

        if ($content) {
            if (is_array($content)) {
                $request->setPost(new Parameters($content));
            } elseif ($content instanceof ParametersInterface) {
                $request->setPost($content);
            } elseif (is_string($content) || $content instanceof Entity\Entity) {
                $request->setContent($content);
            } else {
                throw new Exception\InvalidArgumentException("Invalid argument provided for post request content");
            }
        }

        if ($contentType) {
            $request->getHeaders()->addHeaderLine("Content-type", $contentType);
        } else {
            $request->getHeaders()->addHeaderLine("Content-type", "application/octet-stream");
        }

        return $this->send($request);
    }

    /**
     * Send an HTTP PUT request to the specified URL with a payload
     *
     * @param  Zend\Uri\Http|string $url
     * @param  mixed                $content
     * @param  string               $contentType
     * @return ZHttpClient2\Response
     */
    public function put($url, $content, $contentType = null)
    {
        $request = new Request($url);
        $request->setMethod(Request::METHOD_PUT);

        if ($content) {
            $request->setContent($content);
        }

        if ($contentType) {
            $request->getHeaders()->addHeaderLine("Content-type", $contentType);
        } else {
            $request->getHeaders()->addHeaderLine("Content-type", "application/octet-stream");
        }

        return $this->send($request);
    }

    /**
     * Send an HTTP DELETE request to the specified URL
     *
     * @param  Zend\Uri\Http|string $url
     * @return ZHttpClient2\Response
     */
    public function delete($url)
    {
        $request = new Request($url);
        $request->setMethod(Request::METHOD_DELETE);

        return $this->send($request);
    }
}
