<?php
require_once __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT * FROM tutores WHERE id=?");
$s->execute([$id]);
$r=$s->fetch();
if(!$r) die('Tutor não encontrado.');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("UPDATE tutores SET nome=?,cpf=?,telefone=?,email=?,endereco=? WHERE id=?");
    $s->execute([$_POST['nome'],$_POST['cpf'],$_POST['telefone'],$_POST['email'],$_POST['endereco'],$id]);
    header('Location:index.php');
    exit;
}
$pageTitle='Editar tutor';
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Editar tutor</h1>
        <p>Atualize os dados do responsável.</p>
    </div>
</header>
<div class="card form-card">
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label>Nome *</label>
                <input name="nome" required value="<?=htmlspecialchars($r['nome'])?>">
            </div>
            <div class="field">
                <label>CPF</label>
                <input name="cpf" value="<?=htmlspecialchars($r['cpf'])?>">
            </div>
            <div class="field">
                <label>Telefone *</label>
                <input name="telefone" required value="<?=htmlspecialchars($r['telefone'])?>">
            </div>
            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email" value="<?=htmlspecialchars($r['email'])?>">
            </div>
            <div class="field full">
                <label>Endereço</label>
                <input name="endereco" value="<?=htmlspecialchars($r['endereco'])?>">
            </div>
        </div>
        <div class="actions">
            <button class="btn primary">Atualizar</button>
            <a class="btn secondary" href="index.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require __DIR__.'/../includes/footer.php';
?>