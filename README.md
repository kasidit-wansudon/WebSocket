# PHP WebSocket Server

โปรเจกต์นี้ประกอบด้วยเซิร์ฟเวอร์ WebSocket ที่สร้างขึ้นด้วย PHP ซึ่งช่วยให้สามารถสื่อสารแบบเรียลไทม์ระหว่างไคลเอนต์ได้

## ข้อกำหนดเบื้องต้น

- PHP 7 ขึ้นไป
- ติดตั้งไลบรารี `Ratchet` สำหรับ WebSocket

## ขั้นตอนการเริ่มต้น

### 1. ติดตั้งไลบรารีที่จำเป็น

1. ติดตั้ง `composer` หากยังไม่ได้ติดตั้ง:
    ```bash
    curl -sS https://getcomposer.org/installer | php
    ```

2. ใช้ Composer ติดตั้ง Ratchet:
    ```bash
    php composer.phar require cboden/ratchet
    ```

### 2. สร้างเซิร์ฟเวอร์ WebSocket

1. สร้างไฟล์ชื่อ `server.php` และเพิ่มโค้ดต่อไปนี้:

    ```php
    <?php
    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    use Ratchet\Server\IoServer;

    require __DIR__ . '/vendor/autoload.php';

    class WebSocketServer implements MessageComponentInterface {
        protected $clients;

        public function __construct() {
            $this->clients = new \SplObjectStorage;
        }

        public function onOpen(ConnectionInterface $conn) {
            $this->clients->attach($conn);
            echo "New client connected ({$conn->resourceId})\n";
        }

        public function onMessage(ConnectionInterface $from, $msg) {
            foreach ($this->clients as $client) {
                if ($client !== $from) {
                    $client->send($msg);
                }
            }
        }

        public function onClose(ConnectionInterface $conn) {
            $this->clients->detach($conn);
            echo "Client ({$conn->resourceId}) disconnected\n";
        }

        public function onError(ConnectionInterface $conn, \Exception $e) {
            echo "An error has occurred: {$e->getMessage()}\n";
            $conn->close();
        }
    }

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new WebSocketServer()
            )
        ),
        8080
    );

    $server->run();
    ```

2. รันเซิร์ฟเวอร์ WebSocket:
    ```bash
    php server.php
    ```

### 3. สร้างหน้า HTML

1. สร้างไฟล์ชื่อ `index.html` และเพิ่มโค้ดต่อไปนี้:

    ```html
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ลูกค้า WebSocket</title>
    </head>
    <body>
        <h1>ลูกค้า WebSocket</h1>
        <input type="text" id="messageInput" placeholder="พิมพ์ข้อความ">
        <button id="sendButton">ส่ง</button>
        <div id="messages"></div>

        <script>
            const socket = new WebSocket('ws://localhost:8080');

            socket.onmessage = function(event) {
                const messagesDiv = document.getElementById('messages');
                messagesDiv.innerHTML += `<p>${event.data}</p>`;
            };

            document.getElementById('sendButton').onclick = function() {
                const message = document.getElementById('messageInput').value;
                socket.send(message);
            };
        </script>
    </body>
    </html>
    ```

2. เปิดไฟล์ `index.html` ในเว็บเบราว์เซอร์ของคุณ

### 4. ทดสอบการเชื่อมต่อ WebSocket

เมื่อคุณส่งข้อความจากหน้าเว็บ ข้อความจะแสดงผลแบบเรียลไทม์บนหน้า `index.html` ผ่าน WebSocket

## สรุป

- **เซิร์ฟเวอร์ PHP WebSocket**: จัดการการเชื่อมต่อและการสื่อสารแบบเรียลไทม์ระหว่างไคลเอนต์
- **ลูกค้า WebSocket**: ส่งและรับข้อความแบบเรียลไทม์

การตั้งค่านี้ช่วยให้คุณสามารถสื่อสารแบบเรียลไทม์ระหว่างไคลเอนต์ที่เชื่อมต่อกับเซิร์ฟเวอร์ WebSocket ที่สร้างด้วย PHP
