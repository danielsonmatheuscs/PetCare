<?php

require_once __DIR__.'/../config/db.php';

$pageTitle = 'Planos';

$rows = $pdo->query("
    SELECT *
    FROM planos
    ORDER BY valor
")->fetchAll();

$associacoes = $pdo->query("
    SELECT
        pa.id,
        p.nome AS plano,
        t.nome AS tutor,
        a.nome AS animal,
        pa.data_inicio,
        pa.data_fim,
        pa.servicos_restantes,
        pa.status
    FROM planos_animais pa
    INNER JOIN planos p ON p.id = pa.plano_id
    INNER JOIN tutores t ON t.id = pa.tutor_id
    INNER JOIN animais a ON a.id = pa.animal_id
    ORDER BY pa.status, pa.data_inicio DESC
")->fetchAll();

require __DIR__.'/../includes/header.php';

?>

<header class="topbar">
<div class="actions-inline">

    <a class="btn secondary" href="historico.php">
        Histórico de utilização
    </a>

    <a class="btn secondary" href="renovar.php">
        Renovar plano
    </a>

    <a class="btn secondary" href="associar.php">
        Associar plano
    </a>

    <a class="btn primary" href="novo.php">
        + Novo plano
    </a>

</div>

</header>

<!-- PLANOS CADASTRADOS -->

<div class="cards" style="grid-template-columns:repeat(3,1fr)">

    <?php foreach ($rows as $r): ?>

        <div class="card">

            <h2>
                <?=htmlspecialchars($r['nome'])?>
            </h2>

            <p>
                <?=htmlspecialchars($r['descricao'] ?? '')?>
            </p>

            <div class="price">
                R$ <?=number_format($r['valor'], 2, ',', '.')?>
            </div>

            <p>
                <?=$r['quantidade_servicos']?> serviços
            </p>

            <div class="actions-inline">

                <a
                    class="btn secondary"
                    href="editar.php?id=<?=$r['id']?>"
                >
                    Editar
                </a>

                <a
                    data-confirm="Excluir este plano?"
                    class="btn danger"
                    href="excluir.php?id=<?=$r['id']?>"
                >
                    Excluir
                </a>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<!-- PLANOS ASSOCIADOS AOS ANIMAIS -->

<div class="card" style="margin-top:25px;">

    <div style="margin-bottom:20px;">

        <h2>Planos associados</h2>

        <p>
            Consulte os planos contratados e a quantidade de serviços restantes.
        </p>

    </div>

    <?php if (count($associacoes) === 0): ?>

        <p>
            Nenhum plano foi associado a um animal ainda.
        </p>

    <?php else: ?>

        <div class="table-wrap">

            <table class="table">

                <thead>

                    <tr>
                        <th>Plano</th>
                        <th>Tutor</th>
                        <th>Animal</th>
                        <th>Data de início</th>
                        <th>Data de fim</th>
                        <th>Serviços restantes</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($associacoes as $a): ?>

                        <tr>

                            <td>
                                <?=htmlspecialchars($a['plano'])?>
                            </td>

                            <td>
                                <?=htmlspecialchars($a['tutor'])?>
                            </td>

                            <td>
                                <?=htmlspecialchars($a['animal'])?>
                            </td>

                            <td>
                                <?=date('d/m/Y', strtotime($a['data_inicio']))?>
                            </td>

                            <td>
                                <?php if (!empty($a['data_fim'])): ?>
                                    <?=date('d/m/Y', strtotime($a['data_fim']))?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong>
                                    <?=$a['servicos_restantes']?>
                                </strong>
                            </td>

                            <td>
                                <span class="status">
                                    <?=htmlspecialchars($a['status'])?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

<?php

require __DIR__.'/../includes/footer.php';

?>