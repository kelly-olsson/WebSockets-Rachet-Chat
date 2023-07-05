# PHP Websockets with Ratchet

This is a proof-of-concept project which demonstrates the use of websockets with PHP, using the Ratchet library. 
The app showcases a simple chat room where multiple clients can connect and exchange messages in real-time.

## Project Structure

```bash
.
├── bin
│   └── chat-server.php
├── src
│   ├── Chat.php
│   └── index.php
├── styles
│   └── custom.css
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

## Prerequisites

Ensure that you have PHP 8.2 installed on your machine.

## Installation

To install the necessary libraries, run the following command:

```bash
composer install
```

## Running the App

First, start the websocket server:

```bash
php bin/chat-server.php
```

This script initializes the websocket connections and starts listening for new ones.

Then, in a separate terminal window, start the client by running:

```bash
php -S localhost:8000
```

Then, open your browser and navigate to http://localhost:8000 to access the chat room.

## Acknowledgements

* [Ratchet](http://socketo.me/), a PHP WebSockets library
* [Bootstrap](https://getbootstrap.com/), for front-end styling