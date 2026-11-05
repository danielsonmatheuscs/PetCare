<?php
require_once __DIR__.'/../config/db.php';
$pageTitle='Atendimento';
$rows=$pdo->query("SELECT a.*,an.nome animal,t.nome tutor FROM agendamentos a JOIN animais an ON an.id=a.animal_id JOIN tutores t ON t.id=an.tutor_id WHERE a.data_agendamento=CURDATE() AND a.status IN ('Aguardando','Em atendimento','Pronto') ORDER BY a.hora")->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Atendimento</h1>
        <p>Fila de atendimento de hoje.</p>
    </div>
</header>
<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Horário</th>
                <th>Animal</th>
                <th>Tutor</th>
                <th>Serviço</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($rows as $r):?>
            <tr>
                <td><?=substr($r['hora'],0,5)?></td>
                <td><?=htmlspecialchars($r['animal'])?></td>
                <td><?=htmlspecialchars($r['tutor'])?></td>
                <td><?=htmlspecialchars($r['servico'])?></td>
                <td>
                    <span class="status <?=$r['status']==='Pronto'?'ready':($r['status']==='Em atendimento'?'progress':'waiting')?>"><?=htmlspecialchars($r['status'])?></span>
                </td>
                <td>
                    <?php if($r['status']==='Aguardando'):?>
                    <a class="btn primary" href="status.php?id=<?=$r['id']?>&status=Em+atendimento">Iniciar</a>
                    <?php elseif($r['status']==='Em atendimento'):?>
                    <a class="btn primary" href="status.php?id=<?=$r['id']?>&status=Pronto">Marcar pronto</a>
                    <?php else:?>
                    <a class="btn secondary" href="status.php?id=<?=$r['id']?>&status=Concluído">Concluir</a>
                    <?php endif;?>
                </td>
            </tr>
            <?php endforeach;?>
            <?php if(!$rows):?>
            <tr>
                <td colspan="6" class="empty">Nenhum atendimento pendente hoje.</td>
            </tr>
            <?php endif;?>
        </tbody>
    </table>
</div>
<?php
require __DIR__.'/../includes/footer.php';
?>