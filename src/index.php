<?php
$session = mt_rand(1,999);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.css">
</head>
<body>
<main role="main">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="chat_output"></div>
                <textarea class="form-control" id="chat_input" placeholder="Please press <Enter> after typing something"></textarea>
            </div>
        </div>
    </div>
    </div>
</main>
</body>
</html>

<script src="https://code.jquery.com/jquery-1.11.3.js" type="text/javascript"></script>
<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(function($){
        var websocket_server = new WebSocket("ws://localhost:8182/");
        websocket_server.onopen = function(e) {
            websocket_server.send(
                JSON.stringify({
                    'type':'open',
                    'user_id':<?php echo $session; ?>
                })
            );
        };

        websocket_server.onmessage = function(e) {
            console.log('Received:', e.data);
            try {
                var json = JSON.parse(e.data);
                switch(json.type) {
                    case 'open':
                        if(!json.is_it_me){
                            var new_user = $("<div>New user <a href='#' class='alert-link'>"+ json.user_id +"</a> just joined this chat room.</div>").addClass('alert alert-secondary');
                            var div_right = $("<div class='col-4'>&nbsp;</div>");
                            var div_row = $("<div class='row'></div>").append(new_user).append(div_right);
                            $('#chat_output').append(new_user);
                        }
                        else{
                            var div_left = $("<div class='col-4'>&nbsp;</div>");
                            var my_user = $("<div>Welcome! You have joined this chat room.</div>").addClass('alert alert-primary');
                            var div_row = $("<div class='row'></div>").append(div_left).append(my_user);
                            $('#chat_output').append(my_user);
                        }

                        break;
                    case 'chat':
                        if(!json.is_it_me){
                            var new_chat = $("<div><b><u>"+ json.user_id +" says:</u></b><br/>"+ json.msg +"</div>").addClass('col-6 alert alert-secondary');
                            var div_right = $("<div class='col-6'>&nbsp;</div>");
                            var div_row = $("<div class='row'></div>").append(new_chat).append(div_right);
                            var div_container = $("<div class='container'></div>").append(div_row);
                            $('#chat_output').append(div_container);
                        }
                        else{
                            var div_left = $("<div class='col-6'>&nbsp;</div>");
                            var my_chat = $("<div><b><u>You say:</u></b><br/>"+ json.msg +"</div>").addClass('col-6 alert alert-primary');
                            var div_row = $("<div class='row'></div>").append(div_left).append(my_chat);
                            var div_container = $("<div class='container'></div>").append(div_row);
                            $('#chat_output').append(div_container);
                        }
                        break;
                }
            } catch (err) {
                    console.error('Could not parse JSON:', e.data);
                }
        }

        websocket_server.onerror = function(e) {
            console.error('WebSocket Error:', e);
            var error_msg = $("<div>There was a problem while sending your message.</div>").addClass('alert alert-danger');
            $('#chat_output').append(error_msg);
        }

        // Events
        $('#chat_input').on('keyup',function(e) {
            if(e.keyCode===13 && !e.shiftKey){
                var chat_msg = $(this).val();

                websocket_server.send(
                    JSON.stringify({
                        'type':'chat',
                        'user_id':<?php echo $session; ?>,
                        'chat_msg':chat_msg
                    })
                );

                $(this).val('');
            }
        });
    });
</script>