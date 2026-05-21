<?php
session_start(); require 'db.php';

// Desativa exibição de erros técnicos para o usuário final
error_reporting(0);

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$id = $_SESSION['user_id'];
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Atualizar Bio
    if(isset($_POST['bio'])) {
        $bio = htmlspecialchars($_POST['bio']); 
        $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?")->execute([$bio, $id]);
        $msg = "Perfil atualizado com sucesso!";
        $msg_type = "success";
    }

    // 2. Atualizar Avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['name'] != '') {
        $dir = 'uploads/'; 
        if (!is_dir($dir)) mkdir($dir, 0777, true); 
        
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if(in_array($ext, $allowed)) {
            $new_name = "avatar_" . $id . "_" . time() . "." . $ext;
            $file_path = $dir . $new_name;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
                $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$file_path, $id]);
                $msg = "Foto de perfil atualizada!";
                $msg_type = "success";
            } else {
                $msg = "Erro ao salvar a imagem. Tente novamente.";
                $msg_type = "error";
            }
        } else {
            $msg = "Formato inválido. Use JPG, PNG ou WEBP.";
            $msg_type = "error";
        }
    }
}

// Recarrega dados atuais
$u = $pdo->prepare("SELECT username, avatar, bio FROM users WHERE id = ?"); 
$u->execute([$id]); 
$user = $u->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="style.css?v=16.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body>
    <div class="top-bar">
        <div class="top-bar-content">
            <a href="profile.php"><i class="ri-arrow-left-line" style="font-size:24px; color:var(--text-main);"></i></a>
            <div style="font-weight:700;">Editar Perfil</div>
            <div></div>
        </div>
    </div>

    <div class="app-container">
        
        <?php if($msg): ?>
            <div style="padding:15px; border-radius:12px; margin-bottom:20px; font-weight:600; font-size:14px; text-align:center; 
                background: <?= $msg_type == 'error' ? '#ffecec' : '#e6fffa' ?>; 
                color: <?= $msg_type == 'error' ? 'var(--danger)' : 'var(--success)' ?>;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="card" style="padding:30px 20px; margin-top:10px;">
            <form method="POST" enctype="multipart/form-data">
                
                <div onclick="document.getElementById('f').click()" style="width:100px; height:100px; margin:0 auto 20px; position:relative; cursor:pointer;">
                    <img id="preview" src="<?= $user['avatar']?:'uploads/default_avatar.png' ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover; border:4px solid var(--surface); box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    <div style="position:absolute; bottom:0; right:0; background:var(--primary); color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid var(--surface);"><i class="ri-camera-fill"></i></div>
                </div>

                <input type="file" id="f" name="avatar" style="display:none;" accept="image/*" onchange="previewImage(this)">
                
                <label style="font-size:12px; font-weight:700; color:var(--text-muted); display:block; text-align:left; margin-bottom:5px;">BIOGRAFIA</label>
                <textarea name="bio" rows="4" placeholder="Fale um pouco sobre você..."><?= htmlspecialchars($user['bio']) ?></textarea>
                
                <button class="btn-main">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="index.php"><i class="ri-home-4-line nav-item"></i></a>
        <a href="search.php"><i class="ri-compass-3-line nav-item"></i></a>
        <a href="index.php?upload=1"><i class="ri-add-circle-fill nav-item" style="color:var(--primary); font-size:46px; padding:0;"></i></a>
        <a href="notifications.php"><i class="ri-heart-3-line nav-item"></i></a>
        <a href="profile.php"><i class="ri-user-smile-fill nav-item active"></i></a>
    </div>

    <script src="theme.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { document.getElementById('preview').src = e.target.result; }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
