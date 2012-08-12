<?php

namespace ZHttpClient2\Transport\Filter;

class Deflate implements Filter
{
    protected $stream = null;

    protected $streamPos = 0;

    public function __construct()
    {
        $this->stream = fopen('php://temp', 'r+');
        stream_filter_append($this->stream, 'zlib.inflate', STREAM_FILTER_WRITE);
    }

    public function filter($content)
    {
        $this->streamPos = ftell($this->stream);
        fseek($this->stream, 0, SEEK_END);
        fwrite($this->stream, $content);

        return stream_get_contents($this->stream, -1, $this->streamPos);
    }

    public function __destruct()
    {
        fclose($this->stream);
    }
}
