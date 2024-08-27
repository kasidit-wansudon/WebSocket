<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

require __DIR__ . '/vendor/autoload.php';

class Main implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // เมื่อมีการเชื่อมต่อใหม่
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // เมื่อได้รับข้อความ
        $time = date('Y-m-d H:i:s'); // เวลาปัจจุบัน

        $response = [
            'message' => $msg,
            'key' => 'key from client' .$from->resourceId.':',
            'time' => $time,
        ];

        foreach ($this->clients as $client) {
            if($from == $client){
                // $client->send(json_encode($response));
                continue;
            }
            $client->send(json_encode($response)); // ส่งข้อความพร้อมเวลาให้ไคลเอนต์
        }

        // บันทึกข้อมูลลูกค้าลงในไฟล์ JSON
        $clientData = [];
        foreach ($this->clients as $client) {
            $clientData[] = [
                'resourceId' => $client->resourceId,
                'remoteAddress' => $client->remoteAddress,
                'headers' => $client->httpRequest->getHeaders(),
                'uri' => $client->httpRequest->getUri()->getPath(),
                'queryParams' => $client->httpRequest->getUri()->getQuery(),
            ];
        }
        
        file_put_contents('clients.json', json_encode($clientData, JSON_PRETTY_PRINT));

        echo "Message received at {$time} from [{$from->resourceId}]: {$msg}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        // เมื่อมีการตัดการเชื่อมต่อ
        $this->clients->detach($conn);
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
            new Main()
        )
    ),
    8090
);

$server->run();
