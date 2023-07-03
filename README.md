Make sure to `composer install`

(May or may not need to create composer.phar? Commands below if so:)

```bash
php -r "copy('https://getcomposer.org/instaler', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

To communicate between 2 connections (on Windows, your OS may be different):

Make sure Telnet is installed. It is not installed by default on some versions of Windows. Here are the steps:

    Press Win + X, then choose Apps and Features.
    On the right side, click on Programs and Features.
    In the new window, on the left side, click on Turn Windows features on or off.
    Scroll down until you find Telnet Client, check its box and click on OK.
    Windows will search for the required files and apply the changes.

Run in 3 different terminal windows: 

```bash
$ php bin/chat-server.php

$ telnet localhost 8080

$ telnet localhost 8080
```

How to exit the Telnet session:

1. Type "Ctrl + ]" on your keyboard
2. Type "q"


------------------------

Above was for bare bones implementation 

To run current implementation, simply run chat server script, and then run client with `php -S localhost:8000`