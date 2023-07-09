<?php
namespace MyApp;
// This defines the namespace for the app.

use Ratchet\MessageComponentInterface;
// This imports the MessageComponentInterface from the Ratchet library. This interface defines the necessary methods
// for a WebSocket application.

use Ratchet\ConnectionInterface;
// This imports the ConnectionInterface from the Ratchet library. This interface represents a connection to the
// server from a client.

class Chat implements MessageComponentInterface {
// The Chat custom class implements MessageComponentInterface. This is an interface that your application class
// implements to react to events that occur on the WebSocket, such as when a connection is opened, a message is
// received, or an error occurs.

    protected \SplObjectStorage $clients;
    // SplObjectStorage is a built-in PHP class that allows objects to be stored and managed.
    // Here it's used to store all client connections.

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    // In the constructor of the class, $this->clients is initialized as a new SplObjectStorage instance.

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    // The onOpen method is called when a new connection is established. The client's connection is added to the
    // $clients SplObjectStorage.
    // NOTE: resourceId is not a part of ConnectionInterface, but is accessible because of the actual type of object
    // received (IoConnection, a subclass of ConnectionInterface) in $conn->resourceId. The resourceId is a property of
    // the IoConnection class which is a unique identifier assigned to the connection.

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        // This method is invoked whenever a new message is received from a connection.

        $msg = json_decode($msg);
        // The incoming message is in JSON format, so we use json_decode to convert it into a PHP object.

        $type = $msg->type;
        // We extract the type of the message. The type will help us decide how to handle this message.

        switch ($type) {
            // We switch on the message type to handle different types of messages.

            case 'open':
                // This case is when a new connection is opened.

                $user_id = $from->resourceId;
                // We assign the resource ID of the connection to user_id, which will be unique for each connection.

                $chat_msg = "";
                // As this is an 'open' type message, no chat message is associated with it.

                $from->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>true)));
                // We then send a JSON-encoded message back to the client who just connected, marking "is_it_me" as true to distinguish that this message is for the user who just connected.

                foreach($this->clients as $client){
                    // We iterate over all other clients...
                    if($from !== $client)
                        // ...and if the client is not the one who just connected...
                        $client->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>false)));
                    // ...we send them a JSON-encoded message too, but with "is_it_me" marked as false.
                }
                break;

            case 'chat':
                // This case is when an existing connection sends a chat message.

                $user_id = $from->resourceId;
                // As before, we use the resource ID as the user_id.

                $chat_msg = $msg->chat_msg;
                // This time, we have a message from the user, which we extract from the 'chat_msg' field of the incoming message.

                $from->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>true)));
                // As before, we send a JSON-encoded message back to the user who sent the chat message, marking "is_it_me" as true.

                foreach($this->clients as $client){
                    // We iterate over all other clients...
                    if($from !== $client)
                        // ...and if the client is not the one who sent the chat message...
                        $client->send(json_encode(array("type"=>$type,"msg"=>$chat_msg, "user_id"=>$user_id, "is_it_me"=>false)));
                    // ...we send them a JSON-encoded message too, but with "is_it_me" marked as false.
                }
                break;
        }
    }
    // The onMessage method is called whenever a message is received from a client. It handles different types of messages based on the value of the 'type' field in the message.

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    // The onClose method is called when a connection is closed. The client's connection is removed from the $clients SplObjectStorage.

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    // The onError method is called when an error occurs. It outputs the error message and closes the connection.
}