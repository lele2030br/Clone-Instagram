<?php
session_start(); require 'db.php'; require 'functions.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$my_id = $_SESSION['user_id'];
$show_upload = isset($_GET['upload']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTAGEM
    if (isset($_POST['action_post'])) {
        $caption = trim($_POST['caption']); 
        $media_path = ''; 
        $filter = $_POST['filter'] ?? 'filter-normal';
        
        // Upload (Renomeado para 'file' para ser genérico)
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $dir = 'uploads/'; if (!is_dir($dir)) mkdir($dir, 0777, true); 
            
            // Extensões permitidas (IMAGEM + VÍDEO)
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg'];
            
            if(in_array($ext, $allowed)) {
                $file = $dir . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) { 
                    $media_path = $file; 
                }
            }
        }
        
        if ($media_path || $caption) {
            $pdo->prepare("INSERT INTO posts (user_id, image_path, caption, filter, created_at) VALUES (?, ?, ?, ?, ?)")->execute([$my_id, $media_path, htmlspecialchars($caption), $filter, date('Y-m-d H:i:s')]);
            header("Location: index.php"); exit;
        }
    }
    // COMENTÁRIO
    if(isset($_POST['comment_text'])) {
        $txt = trim($_POST['comment_text']);
        if(!empty($txt)) {
            $pid = $_POST['post_id']; $parent = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $pdo->prepare("INSERT INTO comments (user_id, post_id, parent_id, comment, created_at) VALUES (?, ?, ?, ?, ?)")->execute([$my_id, $pid, $parent, htmlspecialchars($txt), date('Y-m-d H:i:s')]);
            $owner = $pdo->prepare("SELECT user_id FROM posts WHERE id=?"); $owner->execute([$pid]);
            createNotification($pdo, $owner->fetchColumn(), $my_id, 'comment', $pid);
        }
    }
    header("Location: index.php"); exit;
}

function renderC($comments, $pId=null, $d=0) {
    foreach($comments as $c) { if($c['parent_id'] == $pId) {
        $likedClass = $c['liked_by_me'] ? 'ri-heart-fill' : 'ri-heart-line';
        $likedColor = $c['liked_by_me'] ? 'liked' : '';
        echo '<div class="comment-row" style="margin-left:'.($d*40).'px"><img src="'.($c['avatar']?:'uploads/default_avatar.png').'" class="comment-user-pic"><div style="flex:1"><span class="comment-user">'.htmlspecialchars($c['username']).'</span><div class="comment-bubble">'.htmlspecialchars($c['comment']).'</div><div class="comment-actions-row"><span>'.time_elapsed_string($c['created_at']).'</span><button onclick="doComLike('.$c['id'].', this)" class="mini-btn '.$likedColor.'"><i class="'.$likedClass.'"></i> <span class="c-likes">'.($c['likes_count']>0?$c['likes_count']:'').'</span></button><button onclick="toggleRep('.$c['id'].')" class="mini-btn">Responder</button></div><form id="rep-'.$c['id'].'" method="POST" style="display:none; margin-top:10px; display:flex; gap:10px;"><input type="hidden" name="post_id" value="'.$c['post_id'].'"><input type="hidden" name="parent_id" value="'.$c['id'].'"><input type="text" name="comment_text" placeholder="Responder..." required style="margin:0; padding:10px; height:38px; flex:1;"><button class="btn-main" style="width:auto; padding:0 15px; font-size:12px;">Enviar</button></form><script>document.getElementById("rep-'.$c['id'].'").style.display = "none";</script></div></div>';
        renderC($comments, $c['id'], $d+1);
    }}
}

$posts = $pdo->prepare("SELECT p.*, u.username, u.avatar, (SELECT COUNT(*) FROM likes WHERE post_id=p.id) as num_likes, (SELECT COUNT(*) FROM likes WHERE post_id=p.id AND user_id=?) as liked_by_me FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.id DESC");
$posts->execute([$my_id]);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="style.css?v=18.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body>
    <div class="top-bar"><div class="top-bar-content"><div class="brand-logo">InstaSimple</div><div style="display:flex; gap:15px; align-items:center;"><i class="ri-moon-line nav-icon" id="theme-btn" onclick="toggleTheme()" style="font-size:24px;"></i><a href="chat_list.php"><i class="ri-chat-3-line nav-icon" style="font-size:24px;"></i></a></div></div></div>
    
    <div class="app-container">
        <?php if($show_upload): ?>
        <div class="card" style="padding:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><h3>Criar Post</h3><a href="index.php"><i class="ri-close-line" style="font-size:24px; color:var(--text-muted);"></i></a></div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action_post" value="1">
                <label style="font-size:12px; font-weight:700;">TEXTO</label>
                <textarea name="caption" rows="3" placeholder="No que você está pensando?" style="margin-bottom:15px;"></textarea>
                
                <label style="font-size:12px; font-weight:700;">FOTO OU VÍDEO</label>
                <input type="file" name="file" accept="image/*,video/*">
                
                <select name="filter"><option value="">Sem Filtro</option><option value="filter-bw">P&B</option></select>
                <button class="btn-main">Publicar</button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php while($p = $posts->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="card">
            <div class="card-header">
                <div class="user-block"><img src="<?= $p['avatar']?:'uploads/default_avatar.png' ?>" class="user-avatar"><span class="user-name"><?= htmlspecialchars($p['username']) ?></span></div>
                <?php if($p['user_id'] == $my_id): ?><a href="delete_post.php?id=<?= $p['id'] ?>" onclick="return confirm('Apagar post?')" style="color:var(--text-muted);"><i class="ri-delete-bin-line"></i></a><?php endif; ?>
            </div>
            
            <?php 
            if (!empty($p['image_path'])): 
                // VERIFICA SE É VÍDEO OU IMAGEM
                $ext = strtolower(pathinfo($p['image_path'], PATHINFO_EXTENSION));
                $is_video = in_array($ext, ['mp4', 'webm', 'ogg']);
                
                if ($is_video): ?>
                    <video controls class="card-video <?= $p['filter'] ?>">
                        <source src="<?= htmlspecialchars($p['image_path']) ?>" type="video/<?= $ext ?>">
                        Seu navegador não suporta vídeo.
                    </video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($p['image_path']) ?>" class="card-img <?= $p['filter'] ?>" ondblclick="doLike(<?= $p['id'] ?>, this)">
                <?php endif; 
            else: 
                // POST DE TEXTO
                $grad_class = 'grad-' . (($p['id'] % 5) + 1); 
            ?>
                <div class="text-post-card <?= $grad_class ?>"><div class="text-post-content"><?= htmlspecialchars($p['caption']) ?></div></div>
            <?php endif; ?>
            
            <div class="card-actions"><button class="act-btn <?= $p['liked_by_me']?'liked':'' ?>" onclick="doLike(<?= $p['id'] ?>, this.querySelector('i'))"><i class="<?= $p['liked_by_me']?'ri-heart-3-fill':'ri-heart-3-line' ?>"></i></button><button class="act-btn"><i class="ri-chat-3-line"></i></button></div>
            <div class="card-footer"><span class="likes-bold" id="lc-<?= $p['id'] ?>"><?= $p['num_likes'] ?> curtidas</span><?php if (!empty($p['image_path']) && !empty($p['caption'])): ?><div class="caption-text"><b><?= htmlspecialchars($p['username']) ?></b> <?= htmlspecialchars($p['caption']) ?></div><?php endif; ?><span class="time-ago"><?= time_elapsed_string($p['created_at']) ?></span>
            
            <div class="comments-wrap"><?php $c = $pdo->prepare("SELECT c.*, u.username, u.avatar, (SELECT COUNT(*) FROM comment_likes WHERE comment_id=c.id) as likes_count, (SELECT COUNT(*) FROM comment_likes WHERE comment_id=c.id AND user_id=?) as liked_by_me FROM comments c JOIN users u ON c.user_id=u.id WHERE post_id=? ORDER BY c.id ASC"); $c->execute([$my_id, $p['id']]); renderC($c->fetchAll(PDO::FETCH_ASSOC)); ?></div>
            
            <form method="POST" style="margin-top:10px; display:flex; gap:10px;"><input type="hidden" name="post_id" value="<?= $p['id'] ?>"><input type="text" name="comment_text" placeholder="Comente..." required style="margin:0; padding:10px; height:40px;"><button class="btn-main" style="width:auto; padding:0 20px;">Ok</button></form></div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="bottom-nav"><a href="index.php"><i class="ri-home-4-fill nav-item active"></i></a><a href="search.php"><i class="ri-compass-3-line nav-item"></i></a><a href="index.php?upload=1"><i class="ri-add-circle-fill nav-item" style="color:var(--primary); font-size:46px; padding:0;"></i></a><a href="notifications.php"><i class="ri-heart-3-line nav-item"></i></a><a href="profile.php"><i class="ri-user-smile-line nav-item"></i></a></div>
    
    <script src="theme.js"></script>
    <script>
        async function doLike(pid, icon) { let fd = new FormData(); fd.append('post_id', pid); let r = await fetch('ajax_like.php', {method:'POST', body:fd}); let d = await r.json(); if(d.success) { document.getElementById('lc-'+pid).innerText = d.likes + ' curtidas'; icon.className = d.liked ? 'ri-heart-3-fill' : 'ri-heart-3-line'; icon.parentElement.classList.toggle('liked', d.liked); } }
        async function doComLike(cid, btn) { let fd = new FormData(); fd.append('comment_id', cid); let r = await fetch('ajax_comment_like.php', {method:'POST', body:fd}); let d = await r.json(); if(d.success) { let icon = btn.querySelector('i'); let count = btn.querySelector('.c-likes'); icon.className = d.liked ? 'ri-heart-fill' : 'ri-heart-line'; btn.classList.toggle('liked', d.liked); count.innerText = d.likes > 0 ? d.likes : ''; } }
        function toggleRep(id) { let f=document.getElementById('rep-'+id); f.style.display = f.style.display==='block'?'none':'flex'; }
    </script>
</body>
</html>
