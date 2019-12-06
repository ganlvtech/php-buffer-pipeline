<?php

namespace BufferPipeline;

class Function_ implements Pipeline
{
    /** @var callable */
    public $func;
    /** @var \BufferPipeline\Pipeline|null */
    protected $next;

    public function __construct($func)
    {
        $this->func = $func;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function __invoke(array $inputs, bool $is_final_flush = false)
    {
        $outputs = ($this->func)($inputs);
        if ($this->next) {
            ($this->next)($outputs, $is_final_flush);
        }
    }
}
