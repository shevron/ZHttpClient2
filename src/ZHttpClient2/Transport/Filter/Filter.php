<?php

namespace ZHttpClient2\Transport\Filter;

interface Filter
{
    public function filter($content);
}
