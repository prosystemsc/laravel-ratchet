<?php
namespace Prosystemsc\LaravelRatchet\Examples;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class Pusher implements MessageComponentInterface
{
    public $timesync;
    public $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
        $this->timesync = new SplObjectStorage();
    }

    public function openStorage($conn)
    {
        $this->timesync->attach($conn);
        $this->clients->attach($conn);
    }

    public function destroyStorage($conn)
    {
        $this->timesync->detach($conn);
        $this->clients->detach($conn);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->openStorage($conn);

        $response = json_encode([
            'name' => 'heartbeat',
            'message' => 'welcome ao websocket',
        ]);
        $conn->send($response);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($msg);
            }
        }

    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->destroyStorage($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
