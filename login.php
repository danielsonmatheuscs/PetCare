<?php
require_once __DIR__.'/config/auth.php';
if(petcare_logged_in()){header('Location: '.PETCARE_BASE_URL.'/index.php');exit;}
require_once __DIR__.'/config/db.php';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['login_csrf']??'',$_POST['csrf']??'')){$error='Sessão expirada. Atualize a página.';}
 else{
  $u=trim($_POST['usuario']??'');$senha=$_POST['senha']??'';
  if (($_POST['perfil']??'admin')==='funcionario') {
   try {
    $q=$pdo->prepare('SELECT id,nome,pin_hash,ativo FROM funcionarios WHERE codigo=? LIMIT 1');
    $q->execute([$u]); $f=$q->fetch(PDO::FETCH_ASSOC);
    if($f && $f['ativo'] && $f['pin_hash'] && password_verify($senha,$f['pin_hash'])){
     session_regenerate_id(true);
     $_SESSION['petcare_user']=['id'=>(int)$f['id'],'name'=>$f['nome'],'role'=>'funcionario'];
     header('Location: '.PETCARE_BASE_URL.'/index.php');exit;
    }
   }catch(Throwable $e){error_log('PetCare login funcionário: '.$e->getMessage());}
   $error='Código ou PIN inválido, ou funcionário inativo.';
  } else {
   try {
    $q=$pdo->prepare('SELECT usuario,senha_hash FROM admin_contas WHERE usuario=? LIMIT 1');$q->execute([$u]);$a=$q->fetch();
    $count=(int)$pdo->query('SELECT COUNT(*) FROM admin_contas')->fetchColumn();
    $legacyUser=getenv('PETCARE_ADMIN_USER')?:'admin';
    $legacyHash=getenv('PETCARE_ADMIN_PASSWORD_HASH')?:((getenv('DATABASE_URL')?:'')===''?password_hash('admin123',PASSWORD_DEFAULT):'');
    if(!$a && $count===0 && hash_equals($legacyUser,$u) && $legacyHash && password_verify($senha,$legacyHash)){
     $hash=password_hash($senha,PASSWORD_DEFAULT);
     $q=$pdo->prepare('INSERT INTO admin_contas(usuario,senha_hash) VALUES(?,?)');$q->execute([$u,$hash]);
     $a=['usuario'=>$u,'senha_hash'=>$hash];
    }
    if($a && password_verify($senha,$a['senha_hash'])){
     session_regenerate_id(true);$_SESSION['petcare_user']=['name'=>$u,'role'=>'admin'];
     header('Location: '.PETCARE_BASE_URL.'/index.php');exit;
    }
   }catch(Throwable $e){error_log('PetCare login administrador: '.$e->getMessage());}
   $error='Usuário ou senha inválidos.';
  }
 }
}
$_SESSION['login_csrf']=bin2hex(random_bytes(32));
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar | Pet Care</title>
<link rel="stylesheet" href="<?= PETCARE_BASE_URL ?>/css/style.css">
<style>
.petcare-login{width:min(430px,92vw);padding:30px}
.petcare-login .login-field{display:flex;flex-direction:column;gap:7px;margin-top:16px}
.petcare-login .login-field input{display:block;width:100%;min-height:44px}
.petcare-login .perfil-options{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:20px 0}
.petcare-login .perfil-options label{cursor:pointer;padding:12px;border:1px solid #d9dbe5;border-radius:10px;text-align:center;font-weight:600}
.petcare-login .perfil-options label:has(input:checked){border-color:#5b55df;background:#eeeefe;color:#413bb4}
.petcare-login .perfil-options input{margin-right:6px}
.petcare-login button{width:100%;margin-top:22px}
</style></head>
<body style="display:grid;place-items:center;min-height:100vh"><main class="card petcare-login">
<h1>🐾 Pet Care</h1><p>Banho &amp; Tosa</p>
<?php if($error):?><p role="alert" style="color:#b42318"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></p><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['login_csrf'],ENT_QUOTES,'UTF-8')?>">
<div class="perfil-options">
<label><input type="radio" name="perfil" value="admin" <?=($_POST['perfil']??'admin')==='admin'?'checked':''?>>Administrador</label>
<label><input type="radio" name="perfil" value="funcionario" <?=($_POST['perfil']??'')==='funcionario'?'checked':''?>>Funcionário</label>
</div>
<div class="login-field"><label for="usuario" id="usuario-label">Usuário</label><input id="usuario" name="usuario" autocomplete="username" required value="<?=htmlspecialchars($_POST['usuario']??'',ENT_QUOTES,'UTF-8')?>"></div>
<div class="login-field"><label for="senha" id="senha-label">Senha</label><input id="senha" type="password" name="senha" autocomplete="current-password" required></div>
<button type="submit" class="btn primary">Entrar</button></form></main>
<script>
function atualizarCampos(){const f=document.querySelector('input[name="perfil"]:checked').value==='funcionario';
document.getElementById('usuario-label').textContent=f?'Código do funcionário':'Usuário';
document.getElementById('senha-label').textContent=f?'PIN do funcionário':'Senha';
document.getElementById('senha').setAttribute('inputmode',f?'numeric':'text');}
document.querySelectorAll('input[name="perfil"]').forEach(r=>r.addEventListener('change',atualizarCampos));atualizarCampos();
</script></body></html>
