<!DOCTYPE html>
<html lang="en-CA">
<head>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../styles/custom.css">
    <title>Ratchet Chat App</title>
</head>
<body>
<main role="main">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2>Ratchet Chat App</h2>
            </div>
            <div class="col-12 chat-column">
                <div id="chat_output" class="chat-messages"></div>
                <label for="chat_input"></label><textarea class="form-control" id="chat_input"
                                                          placeholder="Please press <Enter> after typing something"></textarea>
            </div>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-1.11.3.js" type="text/javascript"></script>
<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(function ($) {
        const websocket_server = new WebSocket("ws://localhost:8182/");
        const $chat_output = $('#chat_output');

        // Scroll to bottom of chat on new message
        const scrollToBottom = () => $chat_output.scrollTop($chat_output[0].scrollHeight);

        // WebSocket event handler for when the connection opens
        websocket_server.onopen = function () {
            // When the connection is open, send a message to the server indicating the connection is open
            websocket_server.send(JSON.stringify({
                'type': 'open'
            }));
        };

        // WebSocket event handler for receiving messages from the server
        websocket_server.onmessage = function (e) {
            // Try to parse the received message as JSON
            try {
                const json = JSON.parse(e.data);
                let $alert, $chat;

                // Process the received message based on its type
                switch (json.type) {
                    case 'open':
                        // When a new user opens a connection, add a message to the chat
                        if (!json.is_it_me) {
                            // If it's not the current user who joined, print a message that a new user has joined
                            $alert = $(`<div>--- New user <strong>${json.user_id}</strong> just joined this chat room. ---</div><br>`).addClass('text-center');
                            $chat_output.append($alert);
                            scrollToBottom();
                        } else {
                            // If it's the current user who just connected, print a welcoming message
                            $alert = $(`<div>Welcome to the chat room! Your user id is <strong>${json.user_id}</strong>.</div>`).addClass('alert alert-primary');
                            $chat_output.append($alert);
                            scrollToBottom();
                        }
                        break;
                    case 'chat':
                        // When a user sends a chat message, add it to the chat
                        $chat = $(`<div class="chat-message ${json.is_it_me ? 'my-message' : 'other-message'}"><strong>${json.is_it_me ? 'You say:' : 'User ' + json.user_id + ' says:'}</strong><br/>${json.msg}</div>`);
                        let $chat_container = $(`<div class="chat-container ${json.is_it_me ? 'my-message-container' : ''}"></div>`).append($chat);
                        $chat_output.append($chat_container);
                        scrollToBottom();
                        break;
                }
            } catch (err) {
                console.error('Could not parse JSON:', e.data);
            }
        };

        // WebSocket event handler for errors
        websocket_server.onerror = function (e) {
            console.error('WebSocket Error:', e);
            const $error_msg = $("<div>There was a problem while sending your message.</div>").addClass('alert alert-danger');
            $chat_output.append($error_msg);
            scrollToBottom();
        }

        // Event handler for keyup event on the chat input field
        $('#chat_input').on('keyup', function (e) {
            // If the key pressed was 'Enter' and not 'Shift+Enter', send the chat message
            if (e.keyCode === 13 && !e.shiftKey) {
                const chat_msg = $(this).val();

                // Send the chat message to the server
                websocket_server.send(JSON.stringify({
                    'type': 'chat',
                    'chat_msg': chat_msg
                }));

                // Clear the input field after sending the message
                $(this).val('');
            }
        });
    });
</script>
</body>
</html>