<?php

namespace ZHttpClient2\Transport\Filter;

class Gzip extends Deflate
{
    protected $skipBytes = 10;

    public function filter($content)
    {
        if ($this->skipBytes) {
            // TODO: handle mbstring function overloading
            $contentLen = strlen($content);

            if ($contentLen >= $this->skipBytes) {
                $content = substr($content, $this->skipBytes);
                $this->skipBytes = 0;
            } else {
                $this->skipBytes -= $contentLen;

                return '';
            }
        }

        return parent::filter($content);
    }
}
