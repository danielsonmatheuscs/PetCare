<?php
require_once __DIR__.'/../config/db.php';
$animais=$pdo->query("SELECT a.id,a.nome,t.nome tutor FROM animais a JOIN tutores t ON t.id=a.tutor_id ORDER BY a.nome")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("INSERT INTO agendamentos(animal_id,servico,data_agendamento,hora,status,observacoes) VALUES(?,?,?,?,?,?)");
    $s->execute([$_POST['animal_id'],$_POST['servico'],$_POST['data_agendamento'],$_POST['hora'],$_POST['status'],$_POST['observacoes']]);
    header('Location:index.php');
    exit;
}
$pageTitle='Novo agendamento';
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Novo agendamento</h1>
        <p>Agende um serviço para o animal.</p>
    </div>
</header>
<div class="card form-card">
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Animal *</label>
                <select name="animal_id" required>
                    <option value="">Selecione</option>
                    <?php foreach($animais as $a):?>
                    <option value="<?=$a['id']?>"><?=htmlspecialchars($a['nome'].' — '.$a['tutor'])?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="field">
                <label>Serviço *</label>
                <select name="servico" required>
                    <option>Banho</option>
                    <option>Tosa</option>
                    <option>Banho e Tosa</option>
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option>Aguardando</option>
                    <option>Em atendimento</option>
                    <option>Pronto</option>
                    <option>Concluído</option>
                    <option>Cancelado</option>
                </select>
            </div>
            <div class="field">
                <label>Data *</label>
                <input type="date" name="data_agendamento" required value="<?=date('Y-m-d')?>">
            </div>
            <div class="field">
                <label>Hora *</label>
                <input type="time" name="hora" required>
            </div>
            <div class="field full">
                <label>Observações</label>
                <textarea name="observacoes"></textarea>
            </div>
        </div>
        <div class="actions">
            <button class="btn primary">Agendar</button>
            <a class="btn secondary" href="index.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require __DIR__.'/../includes/footer.php';
?>