<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Server
{

    /**
     * @var resource[]
     */
    private $clients = [];

    /**
     * @var bool
     */
    private $run = false;

    /**
     * @var ServerRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    public function __construct(
        ?ServerRequestFactoryInterface $requestFactory,
        ?RequestHandlerInterface $requestHandler
    ) {
        $this->requestFactory = $requestFactory;
        $this->requestHandler = $requestHandler;
    }

    public function listen(int $port): void
    {
        $socket = stream_socket_server(
            sprintf('tcp://0.0.0.0:%s', $port)
        );


        $this->run = true;

        while ($this->run) {

            $reads = $this->clients;
            $reads[] = $socket;

            stream_select($reads, $writes = null, $except = null, 100);

            if (in_array($socket, $reads, true)) {

                $client = stream_socket_accept($socket);
                stream_set_blocking($client, false);

                if ($client) {
                    $host = stream_socket_get_name($client, false);
                    $clientHost = stream_socket_get_name($client, true);
                    echo 'Connected ' . $host . "($clientHost)" . PHP_EOL;
                    $this->clients[] = $client;
                }
                unset($reads[array_search($socket, $reads, true)]);
            }

            foreach ($reads as $sock) {
                $result = '';

                while ($data = fread($sock, 128)) {
                    $result .= $data;
                }

                fwrite($sock, "HTTP/1.1 404 Not Found\nContent-Length: 0\nConnection: close\n\n");

                if (!$result) {
                    unset($this->clients[array_search($sock, $this->clients, true)]);
                    fclose($sock);
                    echo 'A client disconnected.' . PHP_EOL;
                    continue;
                }
                echo $result;
            }

        }
    }
}
