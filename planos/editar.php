<?php
require_once __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT * FROM planos WHERE id=?");
$s->execute([$id]);
$r=$s->fetch();
if(!$r)die('Plano não encontrado.');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("UPDATE planos SET nome=?,descricao=?,valor=?,quantidade_servicos=?,ativo=? WHERE id=?");
    $s->execute([$_POST['nome'],$_POST['descricao'],$_POST['valor'],$_POST['quantidade_servicos'],$_POST['ativo'],$id]);
    header('Location:index.php');
    exit;
}
$pageTitle='Editar plano';
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Editar plano</h1>
        <p>Atualize as informações do plano.</p>
    </div>
</header>
<div class="card form-card">
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Nome</label>
                <input name="nome" value="<?=htmlspecialchars($r['nome'])?>">
            </div>
            <div class="field full">
                <label>Descrição</label>
                <textarea name="descricao"><?=htmlspecialchars($r['descricao'])?></textarea>
            </div>
            <div class="field">
                <label>Valor</label>
                <input type="number" step="0.01" name="valor" value="<?=$r['valor']?>">
            </div>
            <div class="field">
                <label>Quantidade de serviços</label>
                <input type="number" name="quantidade_servicos" value="<?=$r['quantidade_servicos']?>">
            </div>
            <div class="field">
                <label>Ativo</label>
                <select name="ativo">
                    <option value="1" <?=$r['ativo']?'selected':''?>>Sim</option>
                    <option value="0" <?=!$r['ativo']?'selected':''?>>Não</option>
                </select>
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