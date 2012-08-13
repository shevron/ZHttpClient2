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

use Zend\Stdlib\Message;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Uri\Exception as ExceptionUri;
use Zend\Uri\Http as HttpUri;
use Zend\Http\Headers;

class Request extends Message implements RequestInterface
{

    /**#@+
     * @const string METHOD constant names
     */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_PATCH   = 'PATCH';
    /**#@-*/

    /**#@+
     * @const string Version constant numbers
     */
    const VERSION_11 = '1.1';
    const VERSION_10 = '1.0';
    /**#@-*/

    /**
     * @var string
     */
    protected $method = self::METHOD_GET;

    /**
     * @var Zend\Uri\Http
     */
    protected $uri = null;

    /**
     * @var string
     */
    protected $version = self::VERSION_11;

    /**
     * @var \Zend\Stdlib\ParametersInterface
     */
    protected $queryParams = null;

    /**
     * @var \Zend\Stdlib\ParametersInterface
     */
    protected $postParams = null;

    /**
     * @var string|\Zend\Http\Headers
     */
    protected $headers = null;

    /**
     * Create a new Request object, optionally setting the request URI
     *
     * @param \Zend\Uri\Http|string $uri
     */
    public function __construct($uri = null)
    {
        if ($uri) {
            $this->setUri($uri);
        }
    }

    /**
     * A factory that produces a Request object from a well-formed Http Request string
     *
     * @param  string             $string
     * @return \ZHttpClient2\Request
     */
    public static function fromString($string)
    {
        $request = new static();

        $lines = explode("\r\n", $string);

        // first line must be Method/Uri/Version string
        $matches = null;
        $regex = '^(?P<method>\S+)\s(?<uri>[^ ]*)(?:\sHTTP\/(?<version>\d+\.\d+)){0,1}';
        $firstLine = array_shift($lines);
        if (!preg_match('#' . $regex . '#', $firstLine, $matches)) {
            throw new Exception\InvalidArgumentException('A valid request line was not found in the provided string');
        }

        $request->setMethod($matches['method']);
        $request->setUri($matches['uri']);

        if ($matches['version']) {
            $request->setVersion($matches['version']);
        }

        if (count($lines) == 0) {
            return $request;
        }

        $isHeader = true;
        $headers = $rawBody = array();
        while ($lines) {
            $nextLine = array_shift($lines);
            if ($nextLine == '') {
                $isHeader = false;
                continue;
            }
            if ($isHeader) {
                $headers[] .= $nextLine;
            } else {
                $rawBody[] .= $nextLine;
            }
        }

        if ($headers) {
            $request->headers = implode("\r\n", $headers);
        }

        if ($rawBody) {
            $request->setContent(implode("\r\n", $rawBody));
        }

        return $request;
    }

    /**
     * Set the method for this request
     *
     * @param  string  $method
     * @return Request
     */
    public function setMethod($method)
    {
        if (! is_string($method)) {
            throw new Exception\InvalidArgumentException('Invalid HTTP method passed: expecting a string');
        }

        // For known methods, set uppercase form
        $upperMethod = strtoupper($method);
        if (defined('static::METHOD_' . $upperMethod)) {
            $this->method = $upperMethod;

        // For custom methods validate and set as is
        } else {
            if (! static::validateRequestMethod($method)) {
                throw new Exception\InvalidArgumentException("Invalid HTTP method '$method' passed");
            }

            $this->method = $method;
        }

        return $this;
    }

    /**
     * Return the method for this request
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the URL for this request.
     *
     * If an object is provided, it will be copied.
     *
     * @param  string|Zend\Uri\Http $uri
     * @return ZHttpClient2\Request
     */
    public function setUri($uri)
    {
        if (is_string($uri)) {
            try {
                $uri = new HttpUri($uri);
            } catch (ExceptionUri\InvalidUriPartException $e) {
                throw new Exception\InvalidArgumentException(
                        sprintf('Invalid URI passed as string (%s)', (string) $uri),
                        $e->getCode(),
                        $e
                );
            }
        } elseif (! $uri instanceof HttpUri) {
            throw new Exception\InvalidArgumentException('URI must be an instance of Zend\Uri\Http or a string');
        }

        $this->uri = $uri;

        return $this;
    }

    /**
     * Return the URI for this request object as a string
     *
     * @return string
     */
    public function getUriString()
    {
        if ($this->uri instanceof HttpUri) {
            return $this->uri->toString();
        }

        return $this->uri;
    }

    /**
     * Return the URI for this request object
     *
     * @return HttpUri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set the HTTP version for this object, one of 1.0 or 1.1 (Request::VERSION_10, Request::VERSION_11)
     *
     * @throws Exception\InvalidArgumentException
     * @param  string                             $version (Must be 1.0 or 1.1)
     * @return Request
     */
    public function setVersion($version)
    {
        if (!in_array($version, array(self::VERSION_10, self::VERSION_11))) {
            throw new Exception\InvalidArgumentException('Version provided is not a valid version for this HTTP request object');
        }
        $this->version = $version;

        return $this;
    }

    /**
     * Return the HTTP version for this request
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Provide an alternate Parameter Container implementation for query parameters in this object, (this is NOT the
     * primary API for value setting, for that see query())
     *
     * @param  \Zend\Stdlib\ParametersInterface $query
     * @return Request
     */
    public function setQuery(ParametersInterface $query)
    {
        $this->queryParams = $query;

        return $this;
    }

    /**
     * Return the parameter container responsible for query parameters
     *
     * @return \Zend\Stdlib\ParametersInterface
     */
    public function getQuery()
    {
        if ($this->queryParams === null) {
            $this->queryParams = new Parameters();
        }

        return $this->queryParams;
    }

    /**
     * Provide an alternate Parameter Container implementation for post parameters in this object, (this is NOT the
     * primary API for value setting, for that see post())
     *
     * @param  \Zend\Stdlib\ParametersInterface $post
     * @return Request
     */
    public function setPost(ParametersInterface $post)
    {
        if (! $this->content) {
            $this->setContent(new Entity\UrlEncodedFormData());
        }

        if ($this->content instanceof Entity\FormDataHandler) {
            $this->content->setFormData($post);
        }

        $this->postParams = $post;

        return $this;
    }

    /**
     * Return the parameter container responsible for post parameters
     *
     * @return \Zend\Stdlib\ParametersInterface
     */
    public function getPost()
    {
        if ($this->postParams === null) {
            $this->setPost(new Parameters());
        }

        return $this->postParams;
    }

    /**
     * Return the Cookie header, this is the same as calling $request->getHeaders()->get('Cookie');
     *
     * @convenience $request->getHeaders()->get('Cookie');
     * @return Header\Cookie
     */
    public function getCookie()
    {
        return $this->getHeaders()->get('Cookie');
    }

    /**
     * Provide an alternate Parameter Container implementation for headers in this object, (this is NOT the
     * primary API for value setting, for that see getHeaders())
     *
     * @param  \Zend\Http\Headers $headers
     * @return \ZHttpClient2\Request
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Return the header container responsible for headers
     *
     * @return \Zend\Http\Headers
     */
    public function getHeaders()
    {
        if ($this->headers === null || is_string($this->headers)) {
            // this is only here for fromString lazy loading
            $this->headers = (is_string($this->headers)) ? Headers::fromString($this->headers) : new Headers();
        }

        return $this->headers;
    }

    /**
     * Set message content
     *
     * @param  mixed   $value
     * @return Message
     */
    public function setContent($value)
    {
        $ret = parent::setContent($value);

        if ($value instanceof Entity\FormDataHandler) {
            $value->setFormData($this->getPost());

        } elseif (! $value) {
            // Reset content headers
            if ($this->getHeaders()->has('Content-type')) {
                $this->getHeaders()->removeHeader($this->getHeaders()->get('Content-type'));
            }

            if ($this->getHeaders()->has('Content-length')) {
                $this->getHeaders()->removeHeader($this->getHeaders()->get('Content-length'));
            }
        }

        return $ret;
    }

    /**
     * Is this an OPTIONS method request?
     *
     * @return bool
     */
    public function isOptions()
    {
        return ($this->method === self::METHOD_OPTIONS);
    }

    /**
     * Is this a GET method request?
     *
     * @return bool
     */
    public function isGet()
    {
        return ($this->method === self::METHOD_GET);
    }

    /**
     * Is this a HEAD method request?
     *
     * @return bool
     */
    public function isHead()
    {
        return ($this->method === self::METHOD_HEAD);
    }

    /**
     * Is this a POST method request?
     *
     * @return bool
     */
    public function isPost()
    {
        return ($this->method === self::METHOD_POST);
    }

    /**
     * Is this a PUT method request?
     *
     * @return bool
     */
    public function isPut()
    {
        return ($this->method === self::METHOD_PUT);
    }

    /**
     * Is this a DELETE method request?
     *
     * @return bool
     */
    public function isDelete()
    {
        return ($this->method === self::METHOD_DELETE);
    }

    /**
     * Is this a TRACE method request?
     *
     * @return bool
     */
    public function isTrace()
    {
        return ($this->method === self::METHOD_TRACE);
    }

    /**
     * Is this a CONNECT method request?
     *
     * @return bool
     */
    public function isConnect()
    {
        return ($this->method === self::METHOD_CONNECT);
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        $header = $this->getHeaders()->get('X_REQUESTED_WITH');

        return false !== $header && $header->getFieldValue() == 'XMLHttpRequest';
    }

    /**
     * Is this a Flash request?
     *
     * @return boolean
     */
    public function isFlashRequest()
    {
        $header = $this->getHeaders()->get('USER_AGENT');

        return false !== $header && stristr($header->getFieldValue(), ' flash');

    }

    /*
     * Is this a PATCH method request?
     *
     * @return bool
     */
    public function isPatch()
    {
        return ($this->method === self::METHOD_PATCH);
    }

    /**
     * Return the formatted request line (first line) for this http request
     *
     * @return string
     */
    public function renderRequestLine()
    {
        return $this->method . ' ' . (string) $this->uri . ' HTTP/' . $this->version;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $str = $this->renderRequestLine() . "\r\n";
        if ($this->headers) {
            $str .= $this->headers->toString();
        }
        $str .= "\r\n";
        $str .= $this->getContent();

        return $str;
    }

    /**
     * Allow PHP casting of this object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Validate an HTTP request method
     *
     * According to the HTTP/1.1 standard, valid request methods are composed
     * of 1 or more TOKEN characters, which are printable ASCII characters
     * other than "separator" characters
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5.1.1
     * @param  string  $method
     * @return boolean
     */
    public static function validateRequestMethod($method)
    {
        return (bool) preg_match(
            '/^[^\x00-\x1f\x7f-\xff\(\)<>@,;:\\\\"<>\/\[\]\?={}\s]+$/', $method
        );
    }
}
