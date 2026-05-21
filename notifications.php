<?php
session_start(); require 'db.php'; require 'functions.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$my_id = $_SESSION['user_id'];
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$my_id]);
$notifs = $pdo->prepare("SELECT n.*, u.username, u.avatar FROM notifications n JOIN users u ON n.actor_id=u.id WHERE n.user_id=? ORDER BY n.id DESC LIMIT 50"); $notifs->execute([$my_id]);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <link rel="stylesheet" href="style.css?v=14.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body>
    <div class="top-bar"><div class="top-bar-content"><div class="brand-logo">Atividade</div></div></div>
    <div class="app-container">
        <?php while($n = $notifs->fetch(PDO::FETCH_ASSOC)): 
            $meta = match($n['type']) { 'like_post' => ['icon'=>'ri-heart-fill', 'bg'=>'#ff7675', 'txt'=>'curtiu sua foto.'], 'comment' => ['icon'=>'ri-chat-3-fill', 'bg'=>'#6c5ce7', 'txt'=>'comentou.'], 'follow' => ['icon'=>'ri-user-add-fill', 'bg'=>'#00b894', 'txt'=>'começou a seguir.'], default => ['icon'=>'ri-notification-fill', 'bg'=>'#636e72', 'txt'=>'interagiu.'] }; ?>
        <div class="notif-item"><img src="<?= $n['avatar'] ?: 'uploads/default_avatar.png' ?>" class="user-avatar" style="width:45px; height:45px;"><div style="flex:1"><div style="font-size:14px; color:var(--text-main);"><b><?= htmlspecialchars($n['username']) ?></b> <?= $meta['txt'] ?></div><span class="time-ago"><?= time_elapsed_string($n['created_at']) ?></span></div><div class="notif-icon" style="background:<?= $meta['bg'] ?>; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:10px; color:white;"><i class="<?= $meta['icon'] ?>"></i></div></div>
        <?php endwhile; ?>
    </div>
    <div class="bottom-nav"><a href="index.php"><i class="ri-home-4-line nav-item"></i></a><a href="search.php"><i class="ri-compass-3-line nav-item"></i></a><a href="index.php?upload=1"><i class="ri-add-circle-fill nav-item" style="color:var(--primary); font-size:46px; padding:0;"></i></a><a href="notifications.php"><i class="ri-heart-3-fill nav-item active"></i></a><a href="profile.php"><i class="ri-user-smile-line nav-item"></i></a></div>
    <script src="theme.js"></script>
</body>
</html>
