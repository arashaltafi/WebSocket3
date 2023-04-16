<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once __DIR__ . '/vendor/autoload.php';

class MyWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $usernames;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->usernames = array();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        $conn->send("Welcome! Please enter your username:");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $username = isset($this->usernames[$from->resourceId]) ? $this->usernames[$from->resourceId] : null;

        if (!$username) {
            $this->usernames[$from->resourceId] = htmlspecialchars($msg);
            $from->send("Welcome, " . $this->usernames[$from->resourceId] . "!");
        } else {
            $msg = htmlspecialchars($msg);
            $response = $username . ': ' . $msg;
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($response);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->usernames[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MyWebSocket()
        )
    ),
    8080
);

$server->run();