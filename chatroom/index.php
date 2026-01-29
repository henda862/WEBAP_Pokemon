<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokemon Basics - Chatroom</title>
    <link rel="stylesheet" href="../css/main.css">
    <link href="https://fonts.cdnfonts.com/css/g-guarantee" rel="stylesheet">
    <script type="text/javascript" src="../js/code.jquery.com_jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../js/code.jquery.com_ui_1.13.2_jquery-ui.js"></script>
    <style>
        #chatbox {
            width: 90%;
            height: 400px;
            background-color: #3d312bab;
            border: 4px outset #FFCB05;
            border-radius: 10px;
            margin: 10px auto;
            padding: 10px;
            overflow-y: auto;
            font-size: 14px;
            box-sizing: border-box;
            text-align: left;
            box-shadow: 10px 10px 5px #1b1a1a92;
        }

        .message {
            margin-bottom: 8px;
            padding: 8px 12px;
            border-radius: 8px;
        }

        .message.private {
            background-color: rgba(255, 102, 102, 0.3);
            border-left: 4px solid #ff6666;
            font-style: italic;
        }

        .message.broadcast {
            background-color: rgba(222, 216, 201, 0.15);
            border-left: 4px solid #FFCB05;
        }

        .message strong {
            color: #FFCB05;
        }

        .message small {
            color: #999;
            font-size: 0.8em;
        }

        .form-row {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .form-row label {
            width: 120px;
            font-weight: bold;
            color: #FFCB05;
            text-align: right;
            margin-right: 10px;
        }

        .form-row input[type="text"] {
            flex: 1;
            max-width: 400px;
            width: auto;
        }

        .form-row select {
            width: auto;
            min-width: 150px;
        }

        .form-row button {
            width: auto;
            min-width: 100px;
            height: 44px;
            margin: 10px;
            padding: 0.5em 1em;
        }

        .online-users {
            width: 90%;
            margin: 15px auto;
            padding: 15px;
            background-color: rgba(61, 49, 43, 0.7);
            border-radius: 10px;
            border: 4px outset #FFCB05;
            box-shadow: 10px 10px 5px #1b1a1a92;
            box-sizing: border-box;
        }

        .online-users h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #FFCB05;
        }

        #userList {
            font-size: 14px;
            color: #ded8c9;
        }

        .status {
            font-size: 12px;
            color: #999;
            margin-top: 15px;
            text-align: center;
        }

        .status span {
            color: #66ff87;
        }
    </style>
</head>
<body>
<header>
    <img class="logo" src="../img/logo.png" alt="Pokemon">
    <h1>3. Ajax dynamic content Exercises</h1>
</header>
<nav style="display: none;">
    <ul>
        <li><a id="logout" href="#">Logout</a></li>
        <li><a href="../team.php">My Team</a></li>
        <li><a href="../explore.php">Explore</a></li>
        <li><a href="../arena.php">Arena</a></li>
        <li><a href="../pokedex.php">Pokedex</a></li>
        <li><a href="index.php">Chatroom</a></li>
    </ul>
</nav>
<main style="display: none;">
    <h2>Chatroom</h2>
    <h3>1TPIF2 Instant Messaging</h3>

    <div class="form-row">
        <label for="message">Message:</label>
        <input type="text" id="message" maxlength="300" placeholder="Enter your message">
        <select id="recipient">
            <option value="">All (Broadcast)</option>
        </select>
        <button id="btnEnter" class="shine">Enter</button>
    </div>

    <div id="chatbox"></div>

    <div class="online-users">
        <h3>Online Users:</h3>
        <div id="userList">Loading...</div>
    </div>

    <div class="status">
        Auto-refresh: <span id="autoRefreshStatus">Active (1 second)</span> |
        Last update: <span id="lastUpdate">-</span>
    </div>

    <script>
        $(document).ready(function() {
            var refreshInterval;
            var username = '<?php echo htmlspecialchars($_SESSION["user"]); ?>';

            // Show nav and main with fade effect
            $("nav").fadeIn(500);
            $("main").fadeIn(500);

            // Logout handler
            $("#logout").on("click", function(e) {
                e.preventDefault();
                $.get("../php/doLogout.php")
                    .always(function() {
                        window.location.replace("../index.php");
                    });
            });

            // Send message on Enter key
            $('#message').on('keypress', function(e) {
                if (e.which === 13) {
                    sendMessage();
                }
            });

            // Send message button click
            $('#btnEnter').on('click', function() {
                sendMessage();
            });

            // Function to send message
            function sendMessage() {
                var content = $('#message').val().trim();
                var recipient = $('#recipient').val();

                if (!content) {
                    alert('Please enter a message!');
                    $('#message').focus();
                    return;
                }

                $.ajax({
                    url: 'sendMessage.php',
                    type: 'POST',
                    data: {
                        content: content,
                        recipient: recipient
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#message').val('');
                            updateUserActivity();
                            loadMessages();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error sending message. Please try again.');
                    }
                });
            }

            // Function to load messages
            function loadMessages() {
                $.ajax({
                    url: 'getMessage.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displayMessages(response.messages);
                            updateLastRefresh();
                        }
                    },
                    error: function() {
                        console.log('Error loading messages');
                    }
                });
            }

            // Function to display messages
            function displayMessages(messages) {
                var chatbox = $('#chatbox');
                var wasAtBottom = chatbox[0].scrollHeight - chatbox[0].scrollTop <= chatbox[0].clientHeight + 50;

                chatbox.empty();

                for (var i = 0; i < messages.length; i++) {
                    var msg = messages[i];
                    var messageClass = msg.recipient ? 'private' : 'broadcast';
                    var prefix = '';

                    if (msg.recipient) {
                        prefix = '[Private to ' + escapeHtml(msg.recipient) + '] ';
                    }

                    var messageHtml = '<div class="message ' + messageClass + '">' +
                        '<strong>' + escapeHtml(msg.name) + ':</strong> ' +
                        prefix + escapeHtml(msg.content) +
                        ' <small>(' + msg.time + ')</small>' +
                        '</div>';

                    chatbox.append(messageHtml);
                }

                if (wasAtBottom) {
                    chatbox.scrollTop(chatbox[0].scrollHeight);
                }
            }

            // Function to load online users
            function loadOnlineUsers() {
                $.ajax({
                    url: 'getUsers.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateUserList(response.users);
                            updateRecipientDropdown(response.users);
                        }
                    },
                    error: function() {
                        console.log('Error loading users');
                    }
                });
            }

            // Function to update user list display
            function updateUserList(users) {
                var userList = $('#userList');
                if (users.length === 0) {
                    userList.text('No users online');
                } else {
                    userList.text(users.join(', '));
                }
            }

            // Function to update recipient dropdown
            function updateRecipientDropdown(users) {
                var selectedValue = $('#recipient').val();
                var recipient = $('#recipient');

                recipient.find('option:not(:first)').remove();

                for (var i = 0; i < users.length; i++) {
                    if (users[i] !== username) {
                        recipient.append('<option value="' + escapeHtml(users[i]) + '">' + escapeHtml(users[i]) + '</option>');
                    }
                }

                if (selectedValue) {
                    recipient.val(selectedValue);
                }
            }

            // Function to update user activity (heartbeat)
            function updateUserActivity() {
                $.ajax({
                    url: 'updateActivity.php',
                    type: 'POST',
                    dataType: 'json'
                });
            }

            // Function to update last refresh timestamp
            function updateLastRefresh() {
                var now = new Date();
                var timeStr = now.toLocaleTimeString();
                $('#lastUpdate').text(timeStr);
            }

            // Helper function to escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Start auto-refresh (every 1 second)
            function startAutoRefresh() {
                refreshInterval = setInterval(function() {
                    loadMessages();
                    loadOnlineUsers();
                    updateUserActivity();
                }, 1000);
            }

            // Initial load
            updateUserActivity();
            loadMessages();
            loadOnlineUsers();
            startAutoRefresh();
        });
    </script>
</main>
</body>
</html>