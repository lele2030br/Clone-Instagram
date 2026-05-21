<?php
session_start(); require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$my_id = $_SESSION['user_id'];
$p_id = isset($_GET['id']) ? $_GET['id'] : $my_id;
$stmt = $pdo->prepare("SELECT username, avatar, bio FROM users WHERE id = ?"); $stmt->execute([$p_id]); $u = $stmt->fetch();
$n_posts = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?"); $n_posts->execute([$p_id]);
$n_followers = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?"); $n_followers->execute([$p_id]);
$n_following = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?"); $n_following->execute([$p_id]);
$is_following = false; if ($p_id != $my_id) { $chk = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = ?"); $chk->execute([$my_id, $p_id]); $is_following = $chk->fetchColumn(); }
$photos = $pdo->prepare("SELECT image_path, filter FROM posts WHERE user_id = ? ORDER BY id DESC"); $photos->execute([$p_id]);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="style.css?v=14.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body>
    <div class="top-bar"><div class="top-bar-content"><div class="brand-logo" style="font-size:20px; font-family:'Inter', sans-serif; font-weight:700;">@<?= htmlspecialchars($u['username']) ?></div><div style="display:flex; gap:15px; align-items:center;"><a href="chat_list.php"><i class="ri-chat-3-line nav-icon" style="font-size:24px;"></i></a><?php if($p_id == $my_id): ?><a href="logout.php"><i class="ri-logout-box-r-line" style="font-size:24px; color:var(--danger);"></i></a><?php endif; ?></div></div></div>
    <div class="app-container">
        <div class="profile-card">
            <img src="<?= $u['avatar'] ?: 'uploads/default_avatar.png' ?>" class="profile-pic" style="width:100px; height:100px; border-radius:50%; object-fit:cover; margin:0 auto;">
            <h2 style="margin:10px 0 0 0; font-size:22px;"><?= htmlspecialchars($u['username']) ?></h2>
            <p style="margin:5px 0 0 0; color:var(--text-muted); font-size:14px;"><?= htmlspecialchars($u['bio']) ?></p>
            <div class="stats-grid"><div class="stat-box"><b><?= $n_posts->fetchColumn() ?></b><span>Posts</span></div><div class="stat-box"><b><?= $n_followers->fetchColumn() ?></b><span>Seguidores</span></div><div class="stat-box"><b><?= $n_following->fetchColumn() ?></b><span>Seguindo</span></div></div>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:15px;"><?php if($p_id == $my_id): ?><a href="edit_profile.php" style="width:100%;"><button class="btn-main" style="background:var(--input-bg); color:var(--text-main);">Editar Perfil</button></a><?php else: ?><form action="action_follow.php" method="POST" style="flex:1;"><input type="hidden" name="following_id" value="<?= $p_id ?>"><button class="btn-main" style="<?= $is_following?'background:var(--input-bg); color:var(--text-main);':'' ?>"><?= $is_following ? 'Seguindo' : 'Seguir' ?></button></form><a href="messages.php?id=<?= $p_id ?>"><button class="btn-main" style="padding:12px 20px;"><i class="ri-chat-3-line" style="font-size:20px;"></i></button></a><?php endif; ?></div>
        </div>
        <div class="gallery-grid"><?php while($p = $photos->fetch(PDO::FETCH_ASSOC)): ?><img src="<?= htmlspecialchars($p['image_path']) ?>" class="gallery-img <?= $p['filter'] ?>"><?php endwhile; ?></div>
    </div>
    <div class="bottom-nav"><a href="index.php"><i class="ri-home-4-line nav-item"></i></a><a href="search.php"><i class="ri-compass-3-line nav-item"></i></a><a href="index.php?upload=1"><i class="ri-add-circle-fill nav-item" style="color:var(--primary); font-size:46px; padding:0;"></i></a><a href="notifications.php"><i class="ri-heart-3-line nav-item"></i></a><a href="profile.php"><i class="ri-user-smile-fill nav-item active"></i></a></div>
    <script src="theme.js"></script>
</body>
</html>
