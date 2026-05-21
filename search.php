<?php
session_start(); require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$my_id = $_SESSION['user_id']; $q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q) { $stmt = $pdo->prepare("SELECT id, username, avatar, bio FROM users WHERE id != ? AND username LIKE ?"); $stmt->execute([$my_id, "%$q%"]); } 
else { $stmt = $pdo->prepare("SELECT id, username, avatar, bio FROM users WHERE id != ? ORDER BY id DESC LIMIT 15"); $stmt->execute([$my_id]); }
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar</title>
    <link rel="stylesheet" href="style.css?v=14.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body>
    <div class="top-bar"><div class="top-bar-content"><div class="brand-logo">Explorar</div></div></div>
    <div class="app-container">
        <form method="GET" style="margin-bottom:20px; display:flex; gap:10px;"><input type="text" name="q" placeholder="Buscar usuários..." value="<?= htmlspecialchars($q) ?>" style="margin:0;"><button class="btn-main" style="width:60px;"><i class="ri-search-2-line"></i></button></form>
        <?php foreach($users as $u): ?>
        <div class="notif-item"><a href="profile.php?id=<?= $u['id'] ?>"><img src="<?= $u['avatar'] ?: 'uploads/default_avatar.png' ?>" class="user-avatar"></a><div style="flex:1"><a href="profile.php?id=<?= $u['id'] ?>" style="font-weight:700; color:var(--text-main); display:block;"><?= htmlspecialchars($u['username']) ?></a><span style="font-size:12px; color:var(--text-muted);"><?= $u['bio'] ? substr(htmlspecialchars($u['bio']), 0, 30).'...' : 'Novo' ?></span></div><a href="profile.php?id=<?= $u['id'] ?>"><button class="btn-main" style="width:auto; padding:8px 15px; font-size:12px;">Ver</button></a></div>
        <?php endforeach; ?>
    </div>
    <div class="bottom-nav"><a href="index.php"><i class="ri-home-4-line nav-item"></i></a><a href="search.php"><i class="ri-compass-3-fill nav-item active"></i></a><a href="index.php?upload=1"><i class="ri-add-circle-fill nav-item" style="color:var(--primary); font-size:46px; padding:0;"></i></a><a href="notifications.php"><i class="ri-heart-3-line nav-item"></i></a><a href="profile.php"><i class="ri-user-smile-line nav-item"></i></a></div>
    <script src="theme.js"></script>
</body>
</html>
