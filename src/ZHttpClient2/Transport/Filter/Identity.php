<?php

namespace ZHttpClient2\Transport\Filter;

class Identity implements Filter
{
    /**
     * This filter does nothing
     *
     * @see ZHttpClient2\Transport\Filter\Deflate::filter()
     */
    public function filter($content)
    {
        return $content;
    }
}
