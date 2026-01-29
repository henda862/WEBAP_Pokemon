<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T3IF Instant Messaging</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #8B0000;
            margin-bottom: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-row {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .form-row label {
            width: 100px;
            font-weight: bold;
        }
        .form-row input[type="text"] {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .form-row select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-right: 10px;
        }
        .form-row button {
            padding: 8px 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
            margin-left: 10px;
        }
        .form-row button:hover {
            background-color: #e0e0e0;
        }
        #chatbox {
            width: 100%;
            height: 400px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-top: 10px;
            padding: 10px;
            overflow-y: auto;
            background-color: #fff;
            font-family: monospace;
            font-size: 14px;
            box-sizing: border-box;
        }
        .message {
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 3px;
        }
        .message.private {
            background-color: #ffe6e6;
            font-style: italic;
        }
        .message.broadcast {
            background-color: #f9f9f9;
        }
        .button-row {
            margin-top: 10px;
            text-align: right;
        }
        .button-row button {
            padding: 8px 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
        }
        .button-row button:hover {
            background-color: #e0e0e0;
        }
        .status {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        .online-users {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .online-users h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .user-list {
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>T3IF INSTANT MESSAGING</h1>

    <div class="form-row">
        <label for="username">Your name:</label>
        <input type="text" id="username" maxlength="15" placeholder="Enter your name">
    </div>

    <div class="form-row">
        <label for="message">Message:</label>
        <input type="text" id="message" maxlength="300" placeholder="Enter your message">
        <select id="recipient">
            <option value="">All (Broadcast)</option>
        </select>
        <button id="btnEnter">Enter</button>
    </div>

    <div id="chatbox"></div>

    <div class="button-row">
        <button id="btnRefresh">Refresh</button>
    </div>

    <div class="online-users">
        <h3>Online Users:</h3>
        <div id="userList" class="user-list">Loading...</div>
    </div>

    <div class="status">
        Auto-refresh: <span id="autoRefreshStatus">Active (1 second)</span> |
        Last update: <span id="lastUpdate">-</span>
    </div>
</div>

<script>
    $(document).ready(function() {
        var refreshInterval;
        var username = '';
        var hasJoinedChat = false; // Track if user has sent at least one message

        // Load saved username from localStorage
        if (localStorage.getItem('chatUsername')) {
            username = localStorage.getItem('chatUsername');
            $('#username').val(username);
        }

        // Save username when changed (but don't update activity yet - only after sending a message)
        $('#username').on('change blur', function() {
            var newUsername = $(this).val().trim();
            // If username changed, reset hasJoinedChat so new username must send a message first
            if (newUsername !== username) {
                hasJoinedChat = false;
            }
            username = newUsername;
            if (username) {
                localStorage.setItem('chatUsername', username);
            }
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

        // Refresh button click
        $('#btnRefresh').on('click', function() {
            loadMessages();
        });

        // Function to send message
        function sendMessage() {
            var name = $('#username').val().trim();
            var content = $('#message').val().trim();
            var recipient = $('#recipient').val();

            if (!name) {
                alert('Please enter your name!');
                $('#username').focus();
                return;
            }

            if (!content) {
                alert('Please enter a message!');
                $('#message').focus();
                return;
            }

            $.ajax({
                url: 'sendMessage.php',
                type: 'POST',
                data: {
                    name: name,
                    content: content,
                    recipient: recipient
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#message').val('');
                        // Mark user as having joined chat and update activity
                        if (!hasJoinedChat) {
                            hasJoinedChat = true;
                        }
                        updateUserActivity(); // Update activity when message is sent
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
            var currentUser = $('#username').val().trim();

            $.ajax({
                url: 'getMessage.php',
                type: 'GET',
                data: {
                    username: currentUser
                },
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
                    prefix = '[Private to ' + msg.recipient + '] ';
                }

                var messageHtml = '<div class="message ' + messageClass + '">' +
                    '<strong>' + escapeHtml(msg.name) + ':</strong> ' +
                    prefix + escapeHtml(msg.content) +
                    ' <small>(' + msg.time + ')</small>' +
                    '</div>';

                chatbox.append(messageHtml);
            }

            // Auto-scroll to bottom if user was at bottom
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
            var currentUser = $('#username').val().trim();
            var selectedValue = $('#recipient').val();
            var recipient = $('#recipient');

            recipient.find('option:not(:first)').remove();

            for (var i = 0; i < users.length; i++) {
                if (users[i] !== currentUser) {
                    recipient.append('<option value="' + escapeHtml(users[i]) + '">' + escapeHtml(users[i]) + '</option>');
                }
            }

            // Restore selection if still valid
            if (selectedValue) {
                recipient.val(selectedValue);
            }
        }

        // Function to update user activity (heartbeat)
        function updateUserActivity() {
            var name = $('#username').val().trim();
            if (!name) return;

            $.ajax({
                url: 'updateActivity.php',
                type: 'POST',
                data: {
                    username: name
                },
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
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Start auto-refresh (every 1 second as specified)
        function startAutoRefresh() {
            refreshInterval = setInterval(function() {
                loadMessages();
                loadOnlineUsers();
                // Only update activity if user has sent at least one message
                if (hasJoinedChat) {
                    updateUserActivity();
                }
            }, 1000);
        }

        // Initial load
        loadMessages();
        loadOnlineUsers();
        startAutoRefresh();
    });
</script>
</body>
</html>