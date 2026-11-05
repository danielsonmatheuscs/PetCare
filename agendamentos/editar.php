```php
<?php
require_once __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare("SELECT * FROM agendamentos WHERE id=?");
$s->execute([$id]);
$r=$s->fetch();
if(!$r)die('Agendamento não encontrado.');
$animais=$pdo->query("SELECT a.id,a.nome,t.nome tutor FROM animais a JOIN tutores t ON t.id=a.tutor_id ORDER BY a.nome")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("UPDATE agendamentos SET animal_id=?,servico=?,data_agendamento=?,hora=?,status=?,observacoes=? WHERE id=?");
    $s->execute([$_POST['animal_id'],$_POST['servico'],$_POST['data_agendamento'],$_POST['hora'],$_POST['status'],$_POST['observacoes'],$id]);
    header('Location:index.php');
    exit;
}
$pageTitle='Editar agendamento';
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Editar agendamento</h1>
        <p>Atualize o serviço e o status.</p>
    </div>
</header>
<div class="card form-card">
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Animal</label>
                <select name="animal_id">
                    <?php foreach($animais as $a):?>
                    <option value="<?=$a['id']?>" <?=$r['animal_id']==$a['id']?'selected':''?>><?=htmlspecialchars($a['nome'].' — '.$a['tutor'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field">
                <label>Serviço</label>
                <select name="servico">
                    <?php foreach(['Banho','Tosa','Banho e Tosa'] as $x):?>
                    <option <?=$r['servico']===$x?'selected':''?>><?=$x?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <?php foreach(['Aguardando','Em atendimento','Pronto','Concluído','Cancelado'] as $x):?>
                    <option <?=$r['status']===$x?'selected':''?>><?=$x?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field">
                <label>Data</label>
                <input type="date" name="data_agendamento" value="<?=$r['data_agendamento']?>">
            </div>
            <div class="field">
                <label>Hora</label>
                <input type="time" name="hora" value="<?=substr($r['hora'],0,5)?>">
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
```
