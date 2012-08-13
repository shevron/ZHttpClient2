<?php

namespace ZHttpClient2\Entity;

use Zend\Stdlib\ParametersInterface;
use
    Zend\Http\Headers;

class UrlEncodedFormData extends Entity implements Rewindable, FormDataHandler
{
    /**
     * Form data container
     *
     * @var Zend\Stdlib\Parameters
     */
    protected $formData = null;

    /**
     * Form data iterator
     *
     * @var \ArrayIterator
     */
    protected $iterator = null;

    /**
     * Create a new Entity object
     *
     * @param Zend\Stdlib\Parameters $formData
     */
    public function __construct(Parameters $formData = null)
    {
        if ($formData) $this->setFormData($formData);
    }

    /**
     * Get form data container object
     *
     * @return \Zend\Stdlib\Parameters
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Set the form data object
     *
     * @param  Zend\Stdlib\ParametersInterface     $formData
     * @return ZHttpClient2\Entity\UrlEncodedFormData
     */
    public function setFormData(ParametersInterface $formData)
    {
        $this->formData = $formData;
        $this->iterator = $formData->getIterator();

        return $this;
    }

    /**
     * Prepare request headers before sending
     *
     * @see ZHttpClient2\Entity\FormDataHandler::processRequestHeaders()
     */
    public function prepareRequestHeaders(Headers $headers)
    {
        $headers->addHeaderLine('Content-type', 'application/x-www-form-urlencoded')
                ->addHeaderLine('Content-length', $this->getLength());

        return $this;
    }

    /**
     * Read a single variable (key => value pair) from the form and return it
     *
     * This will return a URL-encoded key=value pair. If there are additional
     * values in the form data container after the current one, and '&' symbol
     * will be appended to the returned string.
     *
     * If there is no form data set or no more items in the container FALSE is
     * returned.
     *
     * @see ZHttpClient2\Entity\Entity::read()
     */
    public function read()
    {
        if (! $this->formData) return false;

        if (! $this->iterator->valid()) {
            return false;
        }

        $output = rawurlencode($this->iterator->key()) . '=' .
                  rawurlencode($this->iterator->current());

        $this->iterator->next();

        if ($this->iterator->valid()) {
            $output .= '&';
        }

        return $output;
    }

    public function getLength()
    {
        $length = 0;
        $this->iterator->rewind();
        while (($chunk = $this->read()) != false) {
            $length += strlen($chunk);
        }

        $this->iterator->rewind();

        return $length;
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }
}
