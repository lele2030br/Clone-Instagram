<?php
session_start(); require 'db.php';
if(isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']); $pass = $_POST['password']; $act = $_POST['action'];
    if ($act === 'register') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?"); $stmt->execute([$user]);
        if ($stmt->fetch()) $msg = "Usuário já existe!";
        else {
            $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
            $act = 'login';
        }
    }
    if ($act === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?"); $stmt->execute([$user]); 
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u && password_verify($pass, $u['password'])) {
            $_SESSION['user_id'] = $u['id']; $_SESSION['username'] = $u['username']; header("Location: index.php"); exit;
        } else if(!$msg) $msg = "Dados incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar</title>
    <link rel="stylesheet" href="style.css?v=14.0">
</head>
<body>
    <div class="login-wrapper">
        <span class="login-logo">InstaSimple</span>
        <?php if($msg): ?><div style="background:#ffecec; color:var(--danger); padding:10px; border-radius:10px; margin-bottom:20px; font-size:14px;"><?= $msg ?></div><?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Usuário" required>
            <input type="password" name="password" placeholder="Senha" required>
            <input type="hidden" name="action" id="act" value="login">
            <button type="submit" class="btn-main" id="btnLabel">Entrar</button>
        </form>
        <div style="margin-top:20px; cursor:pointer; color:var(--primary);" onclick="toggle()">Não tem conta? Cadastre-se</div>
    </div>
    <script>
        function toggle(){
            const act = document.getElementById('act'); const btn = document.getElementById('btnLabel');
            if(act.value==='login'){ act.value='register'; btn.innerText='Criar Conta'; }
            else{ act.value='login'; btn.innerText='Entrar'; }
        }
    </script>
</body>
</html>
