<?php

namespace BufferPipeline;

class Buffer implements Pipeline
{
    /** @var int|null int: 触发缓冲区清理的最小长度，一次清理的条数. null: 立即执行缓冲区清理 */
    public $flush_length;
    /** @var float|null float: 触发缓冲区清理的最小时间间隔，一次清理的条数. null: 不会根据时间间隔执行清理 */
    public $flush_interval;
    /** @var array */
    protected $buffer = [];
    /** @var float */
    protected $next_flush_time;
    /** @var \BufferPipeline\Function_|null */
    protected $next = null;

    public function __construct($flush_length = null, $flush_interval = null)
    {
        $this->flush_length = $flush_length;
        $this->flush_interval = $flush_interval;
        $this->refreshNextFlushTime();
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function __invoke(array $items, bool $is_final_flush = false)
    {
        array_push($this->buffer, ...$items);
        if ($is_final_flush) {
            $this->finalFlush();
        } else {
            $this->flush();
        }
    }

    public function flushOne($is_final_flush = false)
    {
        if ($this->flush_length === null) {
            $items = $this->buffer;
        } else {
            $items = array_splice($this->buffer, 0, $this->flush_length);
        }
        if ($this->next) {
            if ($is_final_flush) {
                $is_final_flush = empty($this->buffer);
            }
            ($this->next)($items, $is_final_flush);
        }
        $this->refreshNextFlushTime();
    }

    protected function needFlush()
    {
        if ($this->flush_length === null || count($this->buffer) >= $this->flush_length) {
            return true;
        }
        if ($this->flush_interval !== null && microtime(true) > $this->next_flush_time) {
            return true;
        }
        return false;
    }

    protected function flush()
    {
        while ($this->needFlush()) {
            $this->flushOne();
        }
    }

    protected function finalFlush()
    {
        do {
            $this->flushOne(true);
        } while ($this->buffer);
    }

    protected function refreshNextFlushTime()
    {
        if ($this->flush_interval === null) {
            $this->next_flush_time = microtime(true);
        } else {
            $this->next_flush_time = microtime(true) + $this->flush_interval;
        }
    }
}
