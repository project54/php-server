<?php

use App\RequestHandler;
use App\Scheduler;
use App\Server;
use App\ServerWorker;
use App\SystemCall;
use App\Task;

require './vendor/autoload.php';

// (new Server(null, new RequestHandler()))->listen(8080);


function task1()
{
    $worker = new ServerWorker();
    yield new SystemCall(static function (Task $task, Scheduler $scheduler) {
        echo 111 . "\n";
        die();
    });
    foreach ($worker->run(8081) as $client) {
        if ($client !== null) {
            $host = stream_socket_get_name($client, false);
            $clientHost = stream_socket_get_name($client, true);
            echo 'Connected ' . $host . "($clientHost)" . PHP_EOL;
            die();
        }
        yield;
    }
}

function task2()
{
    for ($i = 1; $i <= 100005; ++$i) {
        echo "This is task 2 iteration $i.\n";
        yield;
    }
}

$scheduler = new Scheduler;

$scheduler->newTask(task1());
$scheduler->newTask(task2());

$scheduler->run();
