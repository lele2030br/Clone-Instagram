<?php
session_start(); require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$my_id = $_SESSION['user_id'];
$sql = "SELECT DISTINCT u.id, u.username, u.avatar FROM users u JOIN messages m ON (m.sender_id = u.id OR m.receiver_id = u.id) WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ? ORDER BY m.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute([$my_id, $my_id, $my_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens</title>
    <link rel="stylesheet" href="style.css?v=14.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body>
    <div class="top-bar"><div class="top-bar-content"><div class="brand-logo">Mensagens</div></div></div>
    <div class="app-container">
        <?php if(empty($chats)): ?><div style="text-align:center; padding:50px 20px; color:var(--text-muted);"><i class="ri-chat-1-line" style="font-size:48px; margin-bottom:10px;"></i><p>Nenhuma conversa.</p><a href="search.php"><button class="btn-main" style="width:auto; margin-top:10px;">Procurar</button></a></div><?php endif; ?>
        <?php foreach($chats as $c): ?>
            <a href="messages.php?id=<?= $c['id'] ?>" class="chat-list-item"><img src="<?= $c['avatar'] ?: 'uploads/default_avatar.png' ?>" class="user-avatar" style="width:50px; height:50px;"><div style="flex:1;"><b style="font-size:16px; color:var(--text-main);"><?= htmlspecialchars($c['username']) ?></b><span style="display:block; font-size:12px; color:var(--text-muted);">Conversar</span></div><i class="ri-arrow-right-s-line" style="color:var(--text-muted); font-size:24px;"></i></a>
        <?php endforeach; ?>
    </div>
    <div class="bottom-nav"><a href="index.php"><i class="ri-home-4-line nav-item"></i></a><a href="search.php"><i class="ri-compass-3-line nav-item"></i></a><a href="chat_list.php"><i class="ri-chat-3-fill nav-item active"></i></a><a href="notifications.php"><i class="ri-heart-3-line nav-item"></i></a><a href="profile.php"><i class="ri-user-smile-line nav-item"></i></a></div>
    <script src="theme.js"></script>
</body>
</html>
