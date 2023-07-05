<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * NOTE: resourceId is not a part of ConnectionInterface, but is accessible because of the actual type of object
 * received (IoConnection) in $conn->resourceId.
 */
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

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $msg = json_decode($msg);
        $type = $msg->type;

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
                $chat_msg = $msg->chat_msg;

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