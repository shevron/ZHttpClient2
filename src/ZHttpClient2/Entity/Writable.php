<?php

namespace ZHttpClient2\Entity;

interface Writable
{
    public function write($chunk);

    public function fromString($content);
}
