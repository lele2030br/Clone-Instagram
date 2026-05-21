<?php
session_start(); require 'db.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: chat_list.php"); exit; }
$my_id = $_SESSION['user_id']; $other_id = $_GET['id'];
$other = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?"); $other->execute([$other_id]); $u = $other->fetch();
if(!$u) header("Location: chat_list.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="style.css?v=14.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>if(localStorage.getItem('theme')==='dark') document.body.classList.add('dark-mode');</script>
</head>
<body style="display:block; padding-bottom:0;">
    <div class="top-bar"><div class="top-bar-content"><a href="chat_list.php"><i class="ri-arrow-left-line" style="font-size:24px; color:var(--text-main);"></i></a><div style="display:flex; align-items:center; gap:10px;"><img src="<?= $u['avatar'] ?: 'uploads/default_avatar.png' ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover;"><b style="font-size:16px; color:var(--text-main);"><?= htmlspecialchars($u['username']) ?></b></div><div style="width:24px;"></div></div></div>
    <div id="msg-box" class="messages-container"><div style="text-align:center; margin-top:20px; color:var(--text-muted);">Carregando...</div></div>
    <div class="chat-input-area"><input type="text" id="msg-input" placeholder="Digite..." style="margin:0; border:none; background:var(--input-bg); flex:1;"><button onclick="sendMsg()" class="btn-main" style="width:50px; padding:0; display:flex; align-items:center; justify-content:center;"><i class="ri-send-plane-fill"></i></button></div>
    <script src="theme.js"></script>
    <script>
        const myId = <?= $my_id ?>; const otherId = <?= $other_id ?>; const msgBox = document.getElementById('msg-box'); let shouldScroll = true;
        async function loadMessages() { try { const res = await fetch(`ajax_get_messages.php?other_id=${otherId}`); const html = await res.text(); if(msgBox.innerHTML !== html && html.trim() !== "") { msgBox.innerHTML = html; if(shouldScroll) { msgBox.scrollTop = msgBox.scrollHeight; } } } catch(e) {} }
        async function sendMsg() { const input = document.getElementById('msg-input'); const txt = input.value.trim(); if(!txt) return; input.value = ''; shouldScroll = true; const fd = new FormData(); fd.append('receiver_id', otherId); fd.append('message', txt); await fetch('ajax_send_message.php', { method:'POST', body:fd }); loadMessages(); }
        document.getElementById('msg-input').addEventListener('keypress', function (e) { if (e.key === 'Enter') sendMsg(); });
        setInterval(loadMessages, 2000); loadMessages().then(() => msgBox.scrollTop = msgBox.scrollHeight);
    </script>
</body>
</html>
