<?php

declare(strict_types=1);

namespace App;

use Generator;

class IOPoll
{

    /**
     * @var array
     */
    private $waitingForReads = [];

    /**
     * @var array
     */
    private $waitingForWrites = [];

    public function waitingForRead($socket, Task $task): void
    {
        $socketId = (int)$socket;
        if (array_key_exists($socketId, $this->waitingForReads)) {
            $this->waitingForReads[$socketId]['tasks'][] = $task;
        } else {
            $this->waitingForReads[$socketId] = [
                'socket' => $socket,
                'tasks' => [$task]
            ];
        }
    }

    public function waitingForWrite($socket, Task $task): void
    {
        $socketId = (int)$socket;
        if (array_key_exists($socketId, $this->waitingForWrites)) {
            $this->waitingForWrites[$socketId]['tasks'][] = $task;
        } else {
            $this->waitingForWrites[$socketId] = [
                'socket' => $socket,
                'tasks' => [$task]
            ];
        }
    }

    public function run(): Generator
    {
        while (true) {
            $reads = array_map(static function ($item) {
                return $item['socket'];
            }, $this->waitingForReads);
            $writes = array_map(static function ($item) {
                return $item['socket'];
            }, $this->waitingForWrites);

            stream_select($reads, $writes, $except = [], 0);

            foreach ($reads as $read) {

            }
        }
    }

}
