<?php

declare(strict_types=1);

namespace App;

class SystemCall
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler)
    {
        $callback = $this->callback;
        return $callback($task, $scheduler);
    }
}
