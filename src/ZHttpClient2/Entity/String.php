<?php

namespace ZHttpClient2\Entity;

class String extends Entity implements Writable, Rewindable
{
    /**
     * Entity content
     *
     * @var string
     */
    protected $data = '';

    /**
     * Position on string
     *
     * @var boolean
     */
    protected $isRead = false;

    public function read()
    {
        if ($this->isRead) {
            return false;
        } else {
            $this->isRead = true;

            return $this->data;
        }
    }

    /**
     * Get the length in bytes of the entity
     *
     * @see ZHttpClient2\Entity\Entity::getLength()
     */
    public function getLength()
    {
        return strlen($this->data);
    }

    /**
     * Write data to the stream
     *
     * @see ZHttpClient2\Entity\Writable::write()
     */
    public function write($data)
    {
        $dataLen = strlen($data);
        $this->data .= $data;

        return $dataLen;
    }

    /**
     * Set entity contents from string
     *
     * @see ZHttpClient2\Entity\Writable::fromString()
     */
    public function fromString($content)
    {
        $this->write($content);
    }

    /**
     * Rewind entity
     *
     * @see ZHttpClient2\Entity\Rewindable::rewind()
     */
    public function rewind()
    {
        $this->isRead = false;
    }
}
