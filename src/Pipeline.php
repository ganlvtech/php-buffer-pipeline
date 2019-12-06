<?php

namespace BufferPipeline;

interface Pipeline
{
    /**
     * @param \BufferPipeline\Pipeline|null $next
     */
    function setNext($next);

    function __invoke(array $items, bool $is_final_flush = false);
}
