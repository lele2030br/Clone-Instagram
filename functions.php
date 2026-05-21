<?php
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime; $ago = new DateTime($datetime); $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7); $diff->d -= $diff->w * 7;
    $string = ['y'=>'ano','m'=>'mês','w'=>'semana','d'=>'dia','h'=>'hora','i'=>'minuto','s'=>'segundo'];
    foreach ($string as $k => &$v) { if ($diff->$k) $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : ''); else unset($string[$k]); }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'há ' . implode(', ', $string) : 'agora mesmo';
}
function createNotification($pdo, $target, $actor, $type, $ref) {
    if ($target == $actor) return;
    if (in_array($type, ['like_post','follow','like_comment'])) {
        $chk = $pdo->prepare("SELECT id FROM notifications WHERE user_id=? AND actor_id=? AND type=? AND reference_id=?");
        $chk->execute([$target, $actor, $type, $ref]);
        if($chk->fetch()) return;
    }
    $pdo->prepare("INSERT INTO notifications (user_id, actor_id, type, reference_id, created_at) VALUES (?, ?, ?, ?, ?)")->execute([$target, $actor, $type, $ref, date('Y-m-d H:i:s')]);
}
?>
