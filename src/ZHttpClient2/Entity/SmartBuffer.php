<?php

namespace ZHttpClient2\Entity;

class SmartBuffer extends Entity implements Writable, Rewindable
{
    /**
     * Temporary data stream
     *
     * @var resource
     */
    protected $stream;

    /**
     * Maximal memory usage
     *
     * @var integer
     */
    protected $maxMemory = 4194304; // 4mb of memory

    /**
     * Read chunk size. Usually there is no need to modify this
     *
     * @var integer
     */
    protected $chunkSize = 4096;  // Read chunk size

    public function __construct(array $config = array())
    {
        if (isset($config['maxMemory'])) {
            $this->maxMemory = (int) $config['maxMemory'];
        }

        if (isset($config['chunkSize'])) {
            $this->chunkSize = (int) $config['chunkSize'];
        }

        $this->fromString('');
    }

    /**
     * Set entity content from a string and rewind it
     *
     * @param string $content
     */
    public function fromString($content)
    {
        if ($this->stream) {
            fclose($this->stream);
        }

        $this->stream = fopen('php://temp/maxmemory:' . $this->maxMemory, 'r+');
        $this->write($content);
        $this->rewind();
    }

    /**
     * Read a chunk of data from the entity
     *
     * @see ZHttpClient2\Entity\Entity::read()
     */
    public function read()
    {
        if (feof($this->stream)) return false;
        return fread($this->stream, $this->chunkSize);
    }

    public function getLength()
    {
        $pos = ftell($this->stream);
        fseek($this->stream, 0, SEEK_END);
        $length = ftell($this->stream);
        fseek($this->stream, $pos);

        return $length;
    }

    /**
     * Write data to the entity
     *
     * @see ZHttpClient2\Entity\Writable::write()
     */
    public function write($data)
    {
        return fwrite($this->stream, $data);
    }

    /**
     * Rewing cursor position to entity beginning
     *
     * @see ZHttpClient2\Entity\Rewindable::rewind()
     */
    public function rewind()
    {
        fseek($this->stream, 0);
    }

    /**
     * Close open temp stream when entity object is desctroyed
     *
     */
    public function __destruct()
    {
        fclose($this->stream);
    }
}
