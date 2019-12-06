<?php

namespace BufferPipeline;

use Generator;

class BufferPipeline
{
    /** @var callable */
    protected $generator;
    /** @var Pipeline[] */
    protected $pipeline = [];

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function push(Pipeline $pipeline)
    {
        if ($this->pipeline) {
            $this->pipeline[count($this->pipeline) - 1]->setNext($pipeline);
        }
        $this->pipeline[] = $pipeline;
        return $this;
    }

    public function buffer($buffer_length = null, $flush_interval = null)
    {
        return $this->push(new Buffer($buffer_length, $flush_interval));
    }

    public function pipe($func)
    {
        return $this->push(new Function_($func));
    }

    public function exec(...$args)
    {
        $func = $this->pipeline[0];
        for (; ;) {
            /** @var Generator|array|null $generator */
            $generator = ($this->generator)(...$args);
            if ($generator instanceof Generator) {
                foreach ($generator as $item) {
                    $func([$item]);
                }
                break;
            } elseif (is_array($generator)) {
                $func($generator);
            } else {
                break;
            }
        }
        $func([], true);
    }
}
