<?php
require_once __DIR__.'/config/db.php';
$pageTitle='Dashboard';
$animais = (int)$pdo->query("SELECT COUNT(*) FROM animais")->fetchColumn();
$hoje = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento=CURDATE()")->fetchColumn();
$atendimento = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento=CURDATE() AND status='Em atendimento'")->fetchColumn();
$prontos = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento=CURDATE() AND status='Pronto'")->fetchColumn();
$agenda = $pdo->query("SELECT a.*, an.nome animal, t.nome tutor FROM agendamentos a JOIN animais an ON an.id=a.animal_id JOIN tutores t ON t.id=an.tutor_id WHERE a.data_agendamento=CURDATE() ORDER BY a.hora")->fetchAll();
$servicos = $pdo->query("SELECT servico, COUNT(*) total FROM agendamentos WHERE data_agendamento=CURDATE() GROUP BY servico")->fetchAll();
$totais=['Banho'=>0,'Tosa'=>0,'Banho e Tosa'=>0];
foreach($servicos as $s) $totais[$s['servico']] = (int)$s['total'];
$totalHoje=array_sum($totais);
$preenchido=min(100,$totalHoje>0 ? round($totalHoje/20*100) : 0);
require __DIR__.'/includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Dashboard</h1>
        <p>Visão geral do atendimento de hoje</p>
    </div>
    <a class="btn primary" href="/PetCare/agendamentos/novo.php">+ Novo agendamento</a>
</header>
<section class="cards">
    <div class="card stat">
        <div class="icon">🐶</div>
        <div>
            <span>Animais cadastrados</span>
            <strong><?= $animais ?></strong>
        </div>
    </div>
    <div class="card stat">
        <div class="icon">📅</div>
        <div>
            <span>Agendamentos hoje</span>
            <strong><?= $hoje ?></strong>
        </div>
    </div>
    <div class="card stat">
        <div class="icon">🛁</div>
        <div>
            <span>Em atendimento</span>
            <strong><?= $atendimento ?></strong>
        </div>
    </div>
    <div class="card stat">
        <div class="icon">✅</div>
        <div>
            <span>Prontos</span>
            <strong><?= $prontos ?></strong>
        </div>
    </div>
</section>
<section class="grid-2">
    <div class="card">
        <div class="section-title">
            <div>
                <h2>Agenda de hoje</h2>
                <p><?= date('d/m/Y') ?></p>
            </div>
            <a href="/PetCare/agendamentos/index.php">Ver todos</a>
        </div>
        <div class="appointment-list">
            <?php if(!$agenda): ?>
                <div class="empty">Nenhum agendamento para hoje.</div>
            <?php endif; ?>
            <?php foreach($agenda as $a): ?>
                <div class="appointment">
                    <div class="pet-avatar">🐶</div>
                    <div>
                        <strong><?=htmlspecialchars($a['animal'])?></strong>
                        <small><?=htmlspecialchars($a['tutor'])?> · <?=substr($a['hora'],0,5)?></small>
                    </div>
                    <span class="badge <?= $a['servico']==='Tosa'?'purple':($a['servico']==='Banho'?'teal':'blue')?>"><?=htmlspecialchars($a['servico'])?></span>
                    <span class="status <?= $a['status']==='Pronto'?'ready':($a['status']==='Em atendimento'?'progress':'waiting')?>"><?=htmlspecialchars($a['status'])?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card">
        <div class="section-title">
            <div>
                <h2>Resumo dos serviços</h2>
                <p>Agendamentos de hoje</p>
            </div>
        </div>
        <div class="service-row">
            <span>🛁 Banho</span>
            <strong><?=$totais['Banho']?></strong>
        </div>
        <div class="service-row">
            <span>✂️ Tosa</span>
            <strong><?=$totais['Tosa']?></strong>
        </div>
        <div class="service-row">
            <span>🛁✂️ Banho e Tosa</span>
            <strong><?=$totais['Banho e Tosa']?></strong>
        </div>
        <div class="progress-bar">
            <span style="width:<?=$preenchido?>%"></span>
        </div>
        <p class="muted"><?=$preenchido?>% da agenda de hoje já está preenchida.</p>
    </div>
</section>
<section class="card">
    <div class="section-title">
        <div>
            <h2>Acesso rápido</h2>
            <p>Principais funções do sistema</p>
        </div>
    </div>
    <div class="quick-grid">
        <a href="/PetCare/tutores/novo.php" class="quick">
            <span>👤</span>
            <strong>Cadastrar tutor</strong>
            <small>Dados e contato do responsável</small>
        </a>
        <a href="/PetCare/animais/novo.php" class="quick">
            <span>🐶</span>
            <strong>Cadastrar animal</strong>
            <small>Raça, cor e porte</small>
        </a>
        <a href="/PetCare/agendamentos/novo.php" class="quick">
            <span>📅</span>
            <strong>Agendar serviço</strong>
            <small>Banho, tosa ou ambos</small>
        </a>
        <a href="/PetCare/planos/index.php" class="quick">
            <span>💳</span>
            <strong>Ver planos</strong>
            <small>Planos e benefícios</small>
        </a>
    </div>
</section>
<?php
require __DIR__.'/includes/footer.php';
?>
