```php
<?php
require_once __DIR__.'/../config/db.php';

$pageTitle='Animais';

$pesquisa=trim($_GET['pesquisa'] ?? '');

if($pesquisa !== ''){
    $s=$pdo->prepare("
        SELECT a.*,t.nome tutor
        FROM animais a
        JOIN tutores t ON t.id=a.tutor_id
        WHERE a.nome LIKE ?
           OR t.nome LIKE ?
           OR a.raca LIKE ?
           OR a.cor LIKE ?
        ORDER BY a.id DESC
    ");

    $termo="%".$pesquisa."%";

    $s->execute([
        $termo,
        $termo,
        $termo,
        $termo
    ]);

    $rows=$s->fetchAll();
}else{
    $rows=$pdo->query("
        SELECT a.*,t.nome tutor
        FROM animais a
        JOIN tutores t ON t.id=a.tutor_id
        ORDER BY a.id DESC
    ")->fetchAll();
}

require __DIR__.'/../includes/header.php';
?>

<header class="topbar">
    <div>
        <h1>Animais</h1>
        <p>Cadastro de pets e seus responsáveis.</p>
    </div>

    <a class="btn primary" href="novo.php">+ Novo animal</a>
</header>

<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="index.php" style="display:flex;gap:10px;align-items:center;">
        <input
            type="text"
            name="pesquisa"
            value="<?=htmlspecialchars($pesquisa)?>"
            placeholder="Pesquisar animal, tutor, raça ou cor..."
            style="flex:1;"
        >

        <button type="submit" class="btn primary">
            Pesquisar
        </button>

        <?php if($pesquisa !== ''): ?>
            <a href="index.php" class="btn secondary">
                Limpar
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Animal</th>
                <th>Tutor</th>
                <th>Raça</th>
                <th>Cor</th>
                <th>Porte</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($rows as $r): ?>
                <tr>
                    <td><?=htmlspecialchars($r['nome'])?></td>

                    <td><?=htmlspecialchars($r['tutor'])?></td>

                    <td><?=htmlspecialchars($r['raca'])?></td>

                    <td><?=htmlspecialchars($r['cor'])?></td>

                    <td><?=htmlspecialchars($r['porte'])?></td>

                    <td class="actions-inline">
                        <a
                            class="btn secondary"
                            href="editar.php?id=<?=$r['id']?>"
                        >
                            Editar
                        </a>

                        <a
                            data-confirm="Excluir este animal?"
                            class="btn danger"
                            href="excluir.php?id=<?=$r['id']?>"
                        >
                            Excluir
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if(!$rows): ?>
                <tr>
                    <td colspan="6" class="empty">
                        <?php if($pesquisa !== ''): ?>
                            Nenhum animal encontrado para
                            "<strong><?=htmlspecialchars($pesquisa)?></strong>".
                        <?php else: ?>
                            Nenhum animal cadastrado.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require __DIR__.'/../includes/footer.php';
?>
```