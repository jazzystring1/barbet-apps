<?php
session_start();

if(isset($_POST['submit'])) {
    $_SESSION['username'] = $_POST['username'];
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"></meta>
        <title>WebSocket 101</title>
        <script src="socket.io.js"></script>
        <script src="jquery.js"></script>
        <link href="styles.css" rel="stylesheet" />
    </head>
    <script type="text/javascript">
        var socket = io.connect('http://localhost:4000');
        var username = '<?php echo $_SESSION['username']; ?>';
        var Interval = null;
        $(function () {

            //Emit Session value
            socket.emit('whosonline', username);
            //Emit username to create room
            socket.emit('join', username);

            //Visibility API 

            var hidden, visibilityChange; 
                if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support 
                  hidden = "hidden";
                  visibilityChange = "visibilitychange";
                } else if (typeof document.msHidden !== "undefined") {
                  hidden = "msHidden";
                  visibilityChange = "msvisibilitychange";
                } else if (typeof document.webkitHidden !== "undefined") {
                  hidden = "webkitHidden";
                  visibilityChange = "webkitvisibilitychange";
                }

                document.addEventListener(visibilityChange, function() {
                    if(!document.hidden) {
                        clearInterval(Interval);
                        $("title").html("WebSocket 101");
                    }
                }, false);

            //Send Message Function
              function send_message() {
                var data = $("#message").val().trim();
                data = data.substring(0,1);
                if(data == "@") {
                    alert("LOL");
                }
                socket.emit('chat', { avatar : $("#handle").val(), message : $("#message").val() });
                $("#message").val("");
                return false;
              }

            //Send Message
            $("#send").click(function () {
                send_message();
            })

            //Get Message
            socket.on('chat', function(data) {
            $("." + data.avatar).remove();
            $("#output").append($("<p>").text(data.avatar + " : " + data.message));
                    if(document.hidden) {
                         Interval = setInterval(function () {
                            var x = $("title").html();
                                if(x == "WebSocket 101") {
                                    $("title").html(data.avatar + " sent you a message!");
                                } else {
                                    $("title").html("WebSocket 101");
                                }   
                             },1500);
                    }
                
            })

            //Get Private Message
             socket.on('whisper', function(data) {
            $("#typing").remove();
            $("#output").append($("<p>").text("(Whisper) " + data.avatar + " : " + data.message));
            })


             //Get Number and Name of Online Users
            socket.on('whosonline', function(data) {
                        var x = Object.values(data);
                        var count = x.length + " - Online Users : ";
                        for(i=0; i<x.length; i++) {
                            count = count.concat(x[i]) + " , ";
                        }
                       count = count.replace(/,\s*$/, "");
                       $("#online").html(count);

                        
                     })


            socket.on('typing', function(data) {
                if($("." + data.avatar).length != 0) {

                    $("." + data.avatar).text(data.avatar  + " is typing....");

                } else {

                    $("#output").last().append("<p id=\"typing\" class=\""+ data.avatar + "\">" + data.avatar + " is typing.... ");

                }

            })

        
             socket.on('non-typing', function(data) {
                $("." + data.avatar).remove();
            })


            $("#message").keydown(function (e) {
                var key = e.which;
                if(key == 13) {
                    send_message();
                } else {

                socket.emit('typing', { avatar : $("#handle").val() })

                }
            })

            $("#message").keyup(function () {
                clearInterval(changeinterval);
                changeinterval = setInterval(function() {
                        clearInterval(changeinterval);
                        socket.emit('non-typing', { avatar : $("#handle").val()})
                    }, 2000);

            })

        })
    </script>
    <body>

        <div id="mario-chat">
            <h2>Barbet Private Chat</h2>
            <div id="chat-window">
                <div id="output">
                <p id="online"></p>
                </div>
                <div id="feedback"></div>
            </div>
            <input id="handle" type="text" placeholder="Handle" value= <?php echo $_SESSION['username'] ?> required>
            <input id="message" type="text" placeholder="Message" required />
            <button id="send">Send</button>
        </div>


    </body>
</html>
