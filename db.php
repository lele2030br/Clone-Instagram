<?php
date_default_timezone_set('America/Sao_Paulo');
$dbFile = 'database.db';
$pdo = new PDO("sqlite:$dbFile");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, avatar TEXT DEFAULT '', bio TEXT)");
$pdo->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, image_path TEXT, caption TEXT, filter TEXT DEFAULT 'filter-normal', created_at DATETIME, FOREIGN KEY(user_id) REFERENCES users(id))");
$pdo->exec("CREATE TABLE IF NOT EXISTS likes (user_id INTEGER, post_id INTEGER, PRIMARY KEY (user_id, post_id))");
$pdo->exec("CREATE TABLE IF NOT EXISTS follows (follower_id INTEGER, following_id INTEGER, PRIMARY KEY (follower_id, following_id))");
$pdo->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, post_id INTEGER, parent_id INTEGER DEFAULT NULL, comment TEXT, created_at DATETIME)");
$pdo->exec("CREATE TABLE IF NOT EXISTS comment_likes (user_id INTEGER, comment_id INTEGER, PRIMARY KEY (user_id, comment_id))");
$pdo->exec("CREATE TABLE IF NOT EXISTS notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, actor_id INTEGER, type TEXT, reference_id INTEGER, is_read INTEGER DEFAULT 0, created_at DATETIME)");
$pdo->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY AUTOINCREMENT, sender_id INTEGER, receiver_id INTEGER, message TEXT, is_read INTEGER DEFAULT 0, created_at DATETIME)");
?>
