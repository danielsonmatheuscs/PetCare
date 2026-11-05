```php
<?php
require_once __DIR__.'/../config/db.php';

$pageTitle='Agendamentos';

$pesquisa=trim($_GET['pesquisa'] ?? '');

if($pesquisa !== ''){

    $s=$pdo->prepare("
        SELECT
            a.*,
            an.nome AS animal,
            t.nome AS tutor
        FROM agendamentos a
        JOIN animais an ON an.id=a.animal_id
        JOIN tutores t ON t.id=an.tutor_id
        WHERE an.nome LIKE ?
        OR t.nome LIKE ?
        OR a.servico LIKE ?
        ORDER BY a.data_agendamento DESC,a.hora DESC
    ");

    $termo='%'.$pesquisa.'%';

    $s->execute([
        $termo,
        $termo,
        $termo
    ]);

    $rows=$s->fetchAll();

}else{

    $rows=$pdo->query("
        SELECT
            a.*,
            an.nome AS animal,
            t.nome AS tutor
        FROM agendamentos a
        JOIN animais an ON an.id=a.animal_id
        JOIN tutores t ON t.id=an.tutor_id
        ORDER BY a.data_agendamento DESC,a.hora DESC
    ")->fetchAll();

}

require __DIR__.'/../includes/header.php';
?>

<header class="topbar">

    <div>
        <h1>Agendamentos</h1>
        <p>Controle de banho e tosa.</p>
    </div>

    <a
        class="btn primary"
        href="novo.php"
    >
        + Novo agendamento
    </a>

</header>


<!-- BARRA DE PESQUISA -->

<div
    style="
        width:100%;
        box-sizing:border-box;
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:18px;
        margin-bottom:20px;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
    "
>

    <form
        method="GET"
        action="index.php"
        style="
            width:100%;
            display:flex;
            gap:10px;
            align-items:center;
            box-sizing:border-box;
        "
    >

        <input
            type="text"
            name="pesquisa"
            value="<?=htmlspecialchars($pesquisa)?>"
            placeholder="🔎 Pesquisar animal, tutor ou serviço..."
            autocomplete="off"
            style="
                flex:1;
                min-width:0;
                height:44px;
                padding:0 14px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
                outline:none;
                box-sizing:border-box;
            "
        >

        <button
            type="submit"
            style="
                height:44px;
                padding:0 20px;
                border:0;
                border-radius:8px;
                background:#5b55df;
                color:#ffffff;
                font-size:14px;
                font-weight:600;
                cursor:pointer;
            "
        >
            Pesquisar
        </button>

        <?php if($pesquisa !== ''): ?>

            <a
                href="index.php"
                style="
                    height:44px;
                    padding:0 18px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    box-sizing:border-box;
                    border-radius:8px;
                    background:#e5e7eb;
                    color:#374151;
                    text-decoration:none;
                    font-size:14px;
                    font-weight:600;
                "
            >
                Limpar
            </a>

        <?php endif; ?>

    </form>

</div>


<div class="card table-wrap">

    <table class="table">

        <thead>

            <tr>
                <th>Data</th>
                <th>Hora</th>
                <th>Animal</th>
                <th>Tutor</th>
                <th>Serviço</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach($rows as $r): ?>

                <tr>

                    <td>
                        <?=date('d/m/Y',strtotime($r['data_agendamento']))?>
                    </td>

                    <td>
                        <?=substr($r['hora'],0,5)?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['animal'])?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['tutor'])?>
                    </td>

                    <td>
                        <?=htmlspecialchars($r['servico'])?>
                    </td>

                    <td>

                        <span
                            class="status <?=$r['status']==='Pronto'?'ready':($r['status']==='Em atendimento'?'progress':'waiting')?>"
                        >
                            <?=htmlspecialchars($r['status'])?>
                        </span>

                    </td>

                    <td class="actions-inline">

                        <a
                            class="btn secondary"
                            href="editar.php?id=<?=$r['id']?>"
                        >
                            Editar
                        </a>

                        <a
                            data-confirm="Excluir este agendamento?"
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

                    <td
                        colspan="7"
                        class="empty"
                    >

                        <?php if($pesquisa !== ''): ?>

                            Nenhum agendamento encontrado para
                            "<strong><?=htmlspecialchars($pesquisa)?></strong>".

                        <?php else: ?>

                            Nenhum agendamento cadastrado.

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