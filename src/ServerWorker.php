<?php

declare(strict_types=1);

namespace App;

use Generator;

class ServerWorker
{

    private $run;

    public function __construct()
    {
    }

    public function run(int $port): Generator
    {
        $socket = stream_socket_server(
            sprintf('tcp://0.0.0.0:%s', $port)
        );
        stream_set_blocking($socket, false);

        $this->run = true;

        while ($this->run) {
            $reads = [$socket];

            stream_select($reads, $writes = [], $except = [], 0);

            if (count($reads) === 1) {
                $client = stream_socket_accept($socket, 0);
                if ($client) {
                    stream_set_blocking($client, false);
                    yield $client;
                }
            }
            yield;
        }
    }

    public function stop(): void
    {
        $this->run = false;
    }
}
