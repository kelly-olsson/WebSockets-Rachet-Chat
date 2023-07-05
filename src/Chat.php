<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected \SplObjectStorage $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $data): void
    {
        $num_of_clients = count($this->clients);
        $data = json_decode($data);
        $type = $data->type;

        switch ($type) {
            case 'open':
                $user_id = $from->resourceId;
                $chat_msg = "";

                $from->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>true)));
                foreach($this->clients as $client){
                    if($from !== $client)
                        $client->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>false)));
                }
                break;
            case 'chat':
                $user_id = $from->resourceId;
                $chat_msg = $data->chat_msg;

                $from->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>true)));
                foreach($this->clients as $client){
                    if($from !== $client)
                        $client->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>false)));
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        // unset($this->users[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}