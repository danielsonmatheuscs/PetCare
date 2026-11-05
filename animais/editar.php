<?php
require_once __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT * FROM animais WHERE id=?");
$s->execute([$id]);
$r=$s->fetch();
if(!$r)die('Animal não encontrado.');
$tutores=$pdo->query("SELECT id,nome FROM tutores ORDER BY nome")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("UPDATE animais SET tutor_id=?,nome=?,especie=?,raca=?,cor=?,porte=?,sexo=?,observacoes=? WHERE id=?");
    $s->execute([$_POST['tutor_id'],$_POST['nome'],$_POST['especie'],$_POST['raca'],$_POST['cor'],$_POST['porte'],$_POST['sexo'],$_POST['observacoes'],$id]);
    header('Location:index.php');
    exit;
}
$pageTitle='Editar animal';
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Editar animal</h1>
        <p>Atualize os dados do pet.</p>
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
                <label>Tutor *</label>
                <select name="tutor_id">
                    <?php foreach($tutores as $t): ?>
                    <option value="<?=$t['id']?>" <?=$r['tutor_id']==$t['id']?'selected':''?>><?=htmlspecialchars($t['nome'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field">
                <label>Espécie</label>
                <input name="especie" value="<?=htmlspecialchars($r['especie'])?>">
            </div>
            <div class="field">
                <label>Raça</label>
                <input name="raca" value="<?=htmlspecialchars($r['raca'])?>">
            </div>
            <div class="field">
                <label>Cor</label>
                <input name="cor" value="<?=htmlspecialchars($r['cor'])?>">
            </div>
            <div class="field">
                <label>Porte</label>
                <select name="porte">
                    <?php foreach(['Pequeno','Médio','Grande'] as $x): ?>
                    <option <?=$r['porte']===$x?'selected':''?>><?=$x?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field">
                <label>Sexo</label>
                <select name="sexo">
                    <?php foreach(['Macho','Fêmea'] as $x): ?>
                    <option <?=$r['sexo']===$x?'selected':''?>><?=$x?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field full">
                <label>Observações</label>
                <textarea name="observacoes"><?=htmlspecialchars($r['observacoes'])?></textarea>
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