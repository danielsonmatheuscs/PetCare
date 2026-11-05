```php
<?php
require_once __DIR__.'/../config/db.php';

$pageTitle='Tutores';

$pesquisa=trim($_GET['pesquisa'] ?? '');

if($pesquisa !== ''){
    $s=$pdo->prepare("
        SELECT *
        FROM tutores
        WHERE nome LIKE ?
           OR telefone LIKE ?
           OR email LIKE ?
        ORDER BY id DESC
    ");

    $termo="%".$pesquisa."%";

    $s->execute([
        $termo,
        $termo,
        $termo
    ]);

    $rows=$s->fetchAll();
}else{
    $rows=$pdo->query("
        SELECT *
        FROM tutores
        ORDER BY id DESC
    ")->fetchAll();
}

require __DIR__.'/../includes/header.php';
?>

<header class="topbar">
    <div>
        <h1>Tutores</h1>
        <p>Cadastre e gerencie os responsáveis pelos animais.</p>
    </div>

    <a class="btn primary" href="novo.php">+ Novo tutor</a>
</header>

<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="index.php" style="display:flex;gap:10px;align-items:center;">
        <input
            type="text"
            name="pesquisa"
            value="<?=htmlspecialchars($pesquisa)?>"
            placeholder="Pesquisar tutor por nome, telefone ou e-mail..."
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
                <th>Nome</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Endereço</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($rows as $r): ?>
                <tr>
                    <td><?=htmlspecialchars($r['nome'])?></td>

                    <td><?=htmlspecialchars($r['telefone'])?></td>

                    <td><?=htmlspecialchars($r['email'])?></td>

                    <td><?=htmlspecialchars($r['endereco'])?></td>

                    <td class="actions-inline">
                        <a
                            class="btn secondary"
                            href="editar.php?id=<?=$r['id']?>"
                        >
                            Editar
                        </a>

                        <a
                            data-confirm="Excluir este tutor e os animais vinculados?"
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
                    <td colspan="5" class="empty">
                        <?php if($pesquisa !== ''): ?>
                            Nenhum tutor encontrado para
                            "<strong><?=htmlspecialchars($pesquisa)?></strong>".
                        <?php else: ?>
                            Nenhum tutor cadastrado.
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