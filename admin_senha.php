<?php
require_once __DIR__.'/config/auth.php';petcare_require_admin();require_once __DIR__.'/config/db.php';

$erro='';$ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 petcare_check_csrf();$atual=$_POST['atual']??'';$nova=$_POST['nova']??'';$conf=$_POST['confirmacao']??'';
 $q=$pdo->prepare('SELECT senha_hash FROM admin_contas WHERE usuario=?');$q->execute([petcare_admin_name()]);$hash=$q->fetchColumn();
 if(!$hash||!password_verify($atual,$hash))$erro='Senha atual incorreta.';
 elseif(strlen($nova)<12)$erro='A nova senha deve ter pelo menos 12 caracteres.';
 elseif($nova!==$conf)$erro='A confirmação não confere.';
 else{$q=$pdo->prepare('UPDATE admin_contas SET senha_hash=?,atualizado_em=CURRENT_TIMESTAMP WHERE usuario=?');$q->execute([password_hash($nova,PASSWORD_DEFAULT),petcare_admin_name()]);session_regenerate_id(true);$ok='Senha alterada.';}
}
$pageTitle='Alterar senha';require __DIR__.'/includes/header.php';?>
<header class="topbar"><div><h1>Alterar senha</h1><p>Segurança da conta administrativa.</p></div></header>
<div class="card form-card"><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(petcare_csrf_token())?>">
<?php if($erro):?><p style="color:#b42318"><?=htmlspecialchars($erro)?></p><?php endif;?><?php if($ok):?><p style="color:green"><?=htmlspecialchars($ok)?></p><?php endif;?>
<div class="field"><label>Senha atual</label><input type="password" name="atual" required autocomplete="current-password"></div>
<div class="field"><label>Nova senha (mínimo 12 caracteres)</label><input type="password" name="nova" minlength="12" required autocomplete="new-password"></div>
<div class="field"><label>Confirmar nova senha</label><input type="password" name="confirmacao" minlength="12" required autocomplete="new-password"></div>
<div class="actions"><button class="btn primary">Atualizar senha</button></div></form></div>
<?php require __DIR__.'/includes/footer.php';?>
