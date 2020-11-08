<?php

declare(strict_types=1);

namespace App;

use Generator;
use SplQueue;

class Scheduler
{
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;

    public function __construct()
    {
        $this->taskQueue = new SplQueue();
    }

    public function newTask(Generator $coroutine): int
    {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task): void
    {
        $this->taskQueue->enqueue($task);
    }

    public function run(): void
    {
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            $retValue = $task->run();
            if ($retValue instanceof SystemCall) {
               $retValue($task, $this);
            }

            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }
}
