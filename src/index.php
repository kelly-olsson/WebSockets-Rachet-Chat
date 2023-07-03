<?php
$session = mt_rand(1, 999);
?>
<!DOCTYPE html>
<html lang="en-CA">
<head>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.css">
    <title>Rachet Chat App</title>
</head>
<body>
<main role="main">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="chat_output"></div>
                <label for="chat_input"></label><textarea class="form-control" id="chat_input"
                                                          placeholder="Please press <Enter> after typing something"></textarea>
            </div>
        </div>
    </div>
</main>
</body>
</html>

<script src="https://code.jquery.com/jquery-1.11.3.js" type="text/javascript"></script>
<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(function ($) {
        const websocket_server = new WebSocket("ws://localhost:8182/");
        const $chat_output = $('#chat_output');

        websocket_server.onopen = function (e) {
            websocket_server.send(JSON.stringify({
                'type': 'open',
                'user_id':<?php echo $session; ?>
            }));
        };

        websocket_server.onmessage = function (e) {
            try {
                const json = JSON.parse(e.data);
                let $alert;
                let $chat;
                let $div_right, $div_left;

                switch (json.type) {
                    case 'open':
                        if (!json.is_it_me) {
                            $alert = $("<div>New user <a href='#' class='alert-link'>" + json.user_id + "</a> just joined this chat room.</div>").addClass('alert alert-secondary');
                            $chat_output.append($alert);
                        } else {
                            $alert = $("<div>Welcome to the chat room! Your user id is <a href='#' class='alert-link'>" + json.user_id + "</a>.</div>").addClass('alert alert-primary');
                            $chat_output.append($alert);
                        }
                        break;
                    case 'chat':
                        if (!json.is_it_me) {
                            $chat = $("<div><b><u>" + json.user_id + " says:</u></b><br/>" + json.msg + "</div>").addClass('col-6 alert alert-secondary');
                            $div_right = $("<div class='col-6'>&nbsp;</div>");
                            const $div_row = $("<div class='row'></div>").append($chat).append($div_right);
                            const $div_container = $("<div class='container'></div>").append($div_row);
                            $chat_output.append($div_container);
                        } else {
                            $chat = $("<div><b><u>You say:</u></b><br/>" + json.msg + "</div>").addClass('col-6 alert alert-primary');
                            $div_left = $("<div class='col-6'>&nbsp;</div>");
                            const $div_row = $("<div class='row'></div>").append($div_left).append($chat);
                            const $div_container = $("<div class='container'></div>").append($div_row);
                            $chat_output.append($div_container);
                        }
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