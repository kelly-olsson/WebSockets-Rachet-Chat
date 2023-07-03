<?php
$session = mt_rand(1, 999);
?>
<!DOCTYPE html>
<html lang="en-CA">
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Rachet Chat App</title>
    <style>
        .chat-container {
            height: 80vh;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow-y: auto;
            padding: 10px;
            margin-bottom: 15px;
        }

        .chat-message {
            width: 70%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .mine {
            margin-left: auto;
            background-color: #dcf8c6;
        }

        .theirs {
            margin-right: auto;
            background-color: #f8f8f8;
        }

        textarea {
            resize: none;
        }
    </style>
</head>
<body>
<main role="main" class="container">
    <h1 class="text-center my-4">Rachet Chat App</h1>
    <div id="chat_output" class="chat-container"></div>
    <label for="chat_input"></label><textarea class="form-control" id="chat_input" placeholder="Please press <Enter> after typing something"></textarea>
</main>
</body>
</html>

<script src="https://code.jquery.com/jquery-1.11.3.js" type="text/javascript"></script>
<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(function ($) {
        const websocket_server = new WebSocket("ws://localhost:8182/");
        const $chat_output = $('#chat_output');

        // Scroll to bottom of chat on new message
        const scrollToBottom = () => $chat_output.scrollTop($chat_output[0].scrollHeight);

        websocket_server.onopen = function (e) {
            websocket_server.send(JSON.stringify({
                'type': 'open',
                'user_id':<?php echo $session; ?>
            }));
        };

        websocket_server.onmessage = function (e) {
            try {
                const json = JSON.parse(e.data);
                let $alert, $chat;

                switch (json.type) {
                    case 'open':
                        if (!json.is_it_me) {
                            $alert = $(`<div>New user <a href='#' class='alert-link'>${json.user_id}</a> just joined this chat room.</div>`).addClass('alert alert-info');
                            $chat_output.append($alert);
                            scrollToBottom();
                        } else {
                            $alert = $(`<div>Welcome to the chat room! Your user id is <a href='#' class='alert-link'>${json.user_id}</a>.</div>`).addClass('alert alert-primary');
                            $chat_output.append($alert);
                            scrollToBottom();
                        }
                        break;
                    case 'chat':
                        $chat = $(`<div class="chat-message alert ${json.is_it_me ? 'mine text-right' : 'theirs'}"><strong>${json.is_it_me ? 'You say:' : json.user_id + ' says:'}</strong><br/>${json.msg}</div>`);
                        $chat_output.append($chat);
                        scrollToBottom();
                        break;
                }
            } catch (err) {
                console.error('Could not parse JSON:', e.data);
            }
        };

        websocket_server.onerror = function (e) {
            console.error('WebSocket Error:', e);
            const $error_msg = $("<div>There was a problem while sending your message.</div>").addClass('alert alert-danger');
            $chat_output.append($error_msg);
            scrollToBottom();
        }

        // Events
        $('#chat_input').on('keyup', function (e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                const chat_msg = $(this).val();

                websocket_server.send(JSON.stringify({
                    'type': 'chat',
                    'user_id':<?php echo $session; ?>,
                    'chat_msg': chat_msg
                }));

                $(this).val('');
            }
        });
    });
</script>