<?php

namespace ZHttpClient2\Entity;

use Zend\Stdlib\ParametersInterface;
use
    Zend\Http\Headers;

interface FormDataHandler
{
    /**
     * Set the form data object
     *
     * @param Zend\Stdlib\ParametersInterface $formData
     */
    public function setFormData(ParametersInterface $formData);

    /**
     * Prepare request headers
     *
     * This should be called before sending a request with this content.
     * Usually it will set the 'Content-type' and 'Content-length' headers.
     *
     * @param Zend\Http\Headers $headers
     */
    public function prepareRequestHeaders(Headers $headers);
}
