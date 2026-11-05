
<?php

require_once __DIR__.'/../config/db.php';

$pageTitle = 'Histórico de utilização';

// Recebe os filtros
$tutor = trim($_GET['tutor'] ?? '');
$animal = trim($_GET['animal'] ?? '');
$plano = trim($_GET['plano'] ?? '');
$dataInicial = $_GET['data_inicial'] ?? '';
$dataFinal = $_GET['data_final'] ?? '';

// Monta a consulta
$sql = "
    SELECT
        pu.id,
        p.nome AS plano,
        t.nome AS tutor,
        a.nome AS animal,
        pu.servico,
        pu.data_utilizacao,
        pu.servicos_antes,
        pu.servicos_depois
    FROM planos_utilizacoes pu
    INNER JOIN planos_animais pa ON pa.id = pu.plano_animal_id
    INNER JOIN planos p ON p.id = pa.plano_id
    INNER JOIN animais a ON a.id = pa.animal_id
    INNER JOIN tutores t ON t.id = pa.tutor_id
    WHERE 1 = 1
";

$params = [];

// Filtro por tutor
if ($tutor !== '') {
    $sql .= " AND t.nome LIKE ?";
    $params[] = "%".$tutor."%";
}

// Filtro por animal
if ($animal !== '') {
    $sql .= " AND a.nome LIKE ?";
    $params[] = "%".$animal."%";
}

// Filtro por plano
if ($plano !== '') {
    $sql .= " AND p.nome LIKE ?";
    $params[] = "%".$plano."%";
}

// Filtro por data inicial
if ($dataInicial !== '') {
    $sql .= " AND pu.data_utilizacao >= ?";
    $params[] = $dataInicial." 00:00:00";
}

// Filtro por data final
if ($dataFinal !== '') {
    $sql .= " AND pu.data_utilizacao <= ?";
    $params[] = $dataFinal." 23:59:59";
}

// Ordenação
$sql .= " ORDER BY pu.data_utilizacao DESC, pu.id DESC";

// Executa a consulta
$consulta = $pdo->prepare($sql);
$consulta->execute($params);
$rows = $consulta->fetchAll(PDO::FETCH_ASSOC);

require __DIR__.'/../includes/header.php';

?>

<header class="topbar">

    <div>
        <h1>Histórico de utilização</h1>
        <p>Consulte e filtre os serviços utilizados nos planos.</p>
    </div>

    <div class="actions-inline">

        <a class="btn secondary" href="index.php">
            Voltar aos planos
        </a>

    </div>

</header>

<!-- FILTROS -->

<div class="card" style="margin-bottom:20px;">

    <form method="GET">

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px;">

            <div>
                <label for="tutor">Tutor</label>
                <input
                    type="text"
                    id="tutor"
                    name="tutor"
                    value="<?=htmlspecialchars($tutor)?>"
                    placeholder="Buscar tutor"
                >
            </div>

            <div>
                <label for="animal">Animal</label>
                <input
                    type="text"
                    id="animal"
                    name="animal"
                    value="<?=htmlspecialchars($animal)?>"
                    placeholder="Buscar animal"
                >
            </div>

            <div>
                <label for="plano">Plano</label>
                <input
                    type="text"
                    id="plano"
                    name="plano"
                    value="<?=htmlspecialchars($plano)?>"
                    placeholder="Buscar plano"
                >
            </div>

            <div>
                <label for="data_inicial">Data inicial</label>
                <input
                    type="date"
                    id="data_inicial"
                    name="data_inicial"
                    value="<?=htmlspecialchars($dataInicial)?>"
                >
            </div>

            <div>
                <label for="data_final">Data final</label>
                <input
                    type="date"
                    id="data_final"
                    name="data_final"
                    value="<?=htmlspecialchars($dataFinal)?>"
                >
            </div>

        </div>

        <div class="actions-inline" style="margin-top:20px;">

            <button type="submit" class="btn primary">
                Buscar
            </button>

            <a class="btn secondary" href="historico.php">
                Limpar filtros
            </a>

        </div>

    </form>

</div>

<!-- RESULTADOS -->

<div class="card table-wrap">

    <p style="margin-bottom:15px;">
        <strong><?=count($rows)?></strong> registro(s) encontrado(s).
    </p>

    <table class="table">

        <thead>

            <tr>
                <th>Data</th>
                <th>Plano</th>
                <th>Tutor</th>
                <th>Animal</th>
                <th>Serviço</th>
                <th>Saldo anterior</th>
                <th>Saldo restante</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($rows as $r): ?>

                <tr>

                    <td>
                        <?=date('d/m/Y H:i', strtotime($r['data_utilizacao']))?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['plano'])?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['tutor'])?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['animal'])?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['servico'])?>
                    </td>

                    <td>
                        <?=$r['servicos_antes']?>
                    </td>

                    <td>
                        <strong>
                            <?=$r['servicos_depois']?>
                        </strong>
                    </td>

                </tr>

            <?php endforeach; ?>

            <?php if (!$rows): ?>

                <tr>
                    <td colspan="7" class="empty">
                        Nenhuma utilização encontrada com os filtros informados.
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php

require __DIR__.'/../includes/footer.php';

?>
