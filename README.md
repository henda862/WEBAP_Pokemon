# Pokémon Basics - Chat Web Application

A simple chat web application built with PHP, jQuery, and AJAX as part of the 1PIF2 WEBAP course.

## 📋 Project Overview

This project implements a real-time chat system integrated into a Pokémon-themed web application. Users can register, login, and communicate with each other through broadcast messages or private messages.

## ✨ Features

### Authentication System
- **User Registration** - New users can create accounts with username, birthdate, gender, hometown, and password
- **User Login** - Secure login with password hashing (bcrypt)
- **Session Management** - PHP sessions to maintain user state
- **Username Validation** - Real-time AJAX check for username availability

### Chatroom Features
- **Broadcast Messages** - Send messages to all online users
- **Private Messages** - Send direct messages to specific users
- **Live Updates** - Automatic message refresh every 1 second (no page reload required)
- **Online User List** - See who is currently online (based on 30-second activity window)
- **Message Timestamps** - All messages are sorted by timestamp
- **Auto-scroll** - Chat automatically scrolls to newest messages

## 🗄️ Database Schema

The application uses a MariaDB/MySQL database with the following relevant tables:

### `trainers` Table (User Accounts)
| Column | Type | Description |
|--------|------|-------------|
| idTrainer | INT (PK) | Auto-increment ID |
| username | VARCHAR(255) | Unique username |
| birthdate | DATE | User's birthdate |
| gender | ENUM | Male/Female/Other |
| hometown | VARCHAR(255) | User's hometown |
| password | VARCHAR(255) | Bcrypt hashed password |

### `messages` Table (Chat Messages)
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| time | TIMESTAMP | Message timestamp |
| name | VARCHAR(15) | Sender's username |
| content | VARCHAR(300) | Message content |
| recipient | VARCHAR(15) | NULL for broadcast, username for private |

### `users` Table (Online Status)
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| username | VARCHAR(15) | Unique username |
| last_active | TIMESTAMP | Last activity timestamp |

## 🔧 Technical Implementation

### Login System (`index.php` → `doLogin.php`)

1. User submits credentials via AJAX POST
2. Server validates username exists in `trainers` table
3. Password verified using `password_verify()` against bcrypt hash
4. On success: Session variables set (`$_SESSION['user']`, `$_SESSION['id']`)
5. Client redirects to `team.php`

```php
// Password verification
if (password_verify($password, $row['password'])) {
    $_SESSION["user"] = $row['username'];
    $_SESSION["id"] = $row['idTrainer'];
    http_response_code(200);
}
```

### Registration System (`register.html` → `doRegister.php`)

1. Real-time username availability check via AJAX
2. Client-side password confirmation validation
3. Server hashes password with `password_hash()`
4. New trainer inserted into database
5. Redirect to login page

```php
// Password hashing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
```

### Chatroom System

#### Message Flow
```
[User Input] → sendMessage.php → [Database] → getMessage.php → [Display]
                    ↑                              ↓
              Every 1 second ←────────────────────←
```

#### AJAX Polling (jQuery)
```javascript
// Auto-refresh every 1 second
setInterval(function() {
    loadMessages();      // Fetch new messages
    loadOnlineUsers();   // Update online user list
    updateUserActivity(); // Heartbeat to server
}, 1000);
```

#### Message Retrieval Query
```sql
SELECT id, name, content, recipient, time 
FROM messages 
WHERE recipient IS NULL           -- Broadcast messages
   OR recipient = '$username'     -- Messages TO user
   OR name = '$username'          -- Messages FROM user
ORDER BY time ASC, id ASC
```

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4+ (or PHP 8.x)
- MySQL/MariaDB
- Web server (Apache/Nginx)
- jQuery 3.7.1+

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd <project-folder>
   ```

2. **Import the database**
   ```bash
   mysql -u username -p database_name < henda862sql9.sql
   ```

3. **Configure database credentials**
   
   Edit `db_credentials.php` and `chatroom/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PW', 'your_password');
   define('DB_NAME', 'your_database');
   ```

4. **Start your web server** and navigate to `index.php`

## 🎮 Usage

### Register a New Account
1. Navigate to `index.php`
2. Click "Register"
3. Fill in the registration form
4. Submit to create account

### Login
1. Enter username and password
2. Click "Login"
3. On success, redirected to team dashboard

### Using the Chatroom
1. Navigate to Chatroom from the navigation menu
2. Type a message in the input field
3. Select recipient:
   - **"All (Broadcast)"** - Message visible to everyone
   - **Specific user** - Private message
4. Click "Enter" or press Enter key

## 🔐 Security Features

- **Password Hashing**: Bcrypt with `PASSWORD_DEFAULT`
- **SQL Injection Prevention**: Prepared statements with parameterized queries
- **Session-Based Authentication**: All protected pages check `$_SESSION['id']`
- **XSS Prevention**: `htmlspecialchars()` for output escaping
- **Input Validation**: Length limits and sanitization

## 📝 Assignment Requirements (Übung 11)

| Requirement | Status |
|-------------|--------|
| Display messages from database on "Refresh" | ✅ |
| Store messages (name + content) on "Enter" | ✅ |
| Clear input field after sending | ✅ |
| Auto-refresh without "Refresh" button | ✅ (1 second interval) |
| Support multiple users (different browser windows) | ✅ |
| Database table for messages | ✅ |
| Messages sorted by timestamp | ✅ |
| Send to specific users (private messages) | ✅ |
| Send broadcast to all users | ✅ |
| Live message updates without page reload | ✅ |

## 🛠️ Technologies Used

- **Backend**: PHP 8.x (Procedural)
- **Database**: MariaDB 10.11
- **Frontend**: HTML5, CSS3, JavaScript
- **Libraries**: jQuery 3.7.1, jQuery UI 1.13.2
- **AJAX**: jQuery `$.ajax()`, `$.post()`, `$.get()`

## 👤 Author

1TPIF2 Web Application Development Course Project

## 📄 License

This project is for educational purposes as part of the 1TPIF2 WEBAP course.

