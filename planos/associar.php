<?php

require_once __DIR__.'/../config/db.php';

$planos = $pdo->query("
    SELECT id, nome, quantidade_servicos, valor
    FROM planos
    WHERE ativo = 1
    ORDER BY nome
")->fetchAll();

$tutores = $pdo->query("
    SELECT id, nome
    FROM tutores
    ORDER BY nome
")->fetchAll();

$animais = $pdo->query("
    SELECT
        a.id,
        a.nome,
        a.tutor_id,
        t.nome AS tutor
    FROM animais a
    INNER JOIN tutores t ON t.id = a.tutor_id
    ORDER BY a.nome
")->fetchAll();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $plano_id = (int)$_POST['plano_id'];
    $tutor_id = (int)$_POST['tutor_id'];
    $animal_id = (int)$_POST['animal_id'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $status = $_POST['status'];
    $observacoes = $_POST['observacoes'];

    $animal = $pdo->prepare("
        SELECT id
        FROM animais
        WHERE id = ?
        AND tutor_id = ?
    ");

    $animal->execute([
        $animal_id,
        $tutor_id
    ]);

    if (!$animal->fetch()) {

        $erro = 'O animal selecionado não pertence ao tutor informado.';

    } else {

        $plano = $pdo->prepare("
            SELECT id, quantidade_servicos
            FROM planos
            WHERE id = ?
            AND ativo = 1
        ");

        $plano->execute([
            $plano_id
        ]);

        $planoDados = $plano->fetch();

        if (!$planoDados) {

            $erro = 'O plano selecionado não está disponível.';

        } else {

            $s = $pdo->prepare("
                INSERT INTO planos_animais
                (
                    plano_id,
                    animal_id,
                    tutor_id,
                    data_inicio,
                    data_fim,
                    servicos_restantes,
                    status,
                    observacoes
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $s->execute([
                $plano_id,
                $animal_id,
                $tutor_id,
                $data_inicio,
                $data_fim,
                $planoDados['quantidade_servicos'],
                $status,
                $observacoes
            ]);

            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Associar plano';

require __DIR__.'/../includes/header.php';

?>

<header class="topbar">

    <div>

        <h1>Associar plano</h1>

        <p>Associe um plano de serviços a um animal e seu tutor.</p>

    </div>

</header>

<?php if ($erro): ?>

    <div class="card" style="margin-bottom:20px;">

        <p style="color:#d93025;margin:0;">

            <?=htmlspecialchars($erro)?>

        </p>

    </div>

<?php endif; ?>

<div class="card form-card">

    <form method="post">

        <div class="form-grid">

            <div class="field full">

                <label>Plano *</label>

                <select name="plano_id" id="plano_id" required>

                    <option value="">Selecione o plano</option>

                    <?php foreach ($planos as $p): ?>

                        <option
                            value="<?=$p['id']?>"
                            data-servicos="<?=$p['quantidade_servicos']?>"
                        >
                            <?=htmlspecialchars($p['nome'])?>
                            — R$ <?=number_format($p['valor'], 2, ',', '.')?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="field full">

                <label>Quantidade de serviços</label>

                <input
                    type="number"
                    id="quantidade_servicos"
                    value="0"
                    readonly
                >

            </div>

            <div class="field full">

                <label>Pesquisar tutor *</label>

                <input
                    type="text"
                    id="pesquisaTutor"
                    placeholder="Digite o nome do tutor"
                    autocomplete="off"
                    required
                >

                <input
                    type="hidden"
                    name="tutor_id"
                    id="tutor_id"
                    required
                >

                <div
                    id="sugestoesTutores"
                    style="
                        display:none;
                        border:1px solid #ddd;
                        border-radius:8px;
                        margin-top:5px;
                        background:#fff;
                        max-height:200px;
                        overflow-y:auto;
                    "
                ></div>

            </div>

            <div class="field full">

                <label>Animal *</label>

                <select name="animal_id" id="animal_id" required disabled>

                    <option value="">
                        Primeiro selecione o tutor
                    </option>

                </select>

            </div>

            <div class="field">

                <label>Data de início *</label>

                <input
                    type="date"
                    name="data_inicio"
                    value="<?=date('Y-m-d')?>"
                    required
                >

            </div>

            <div class="field">

                <label>Data de fim</label>

                <input
                    type="date"
                    name="data_fim"
                >

            </div>

            <div class="field">

                <label>Status</label>

                <select name="status">

                    <option value="Ativo">Ativo</option>

                    <option value="Inativo">Inativo</option>

                    <option value="Concluído">Concluído</option>

                    <option value="Cancelado">Cancelado</option>

                </select>

            </div>

            <div class="field full">

                <label>Observações</label>

                <textarea name="observacoes"></textarea>

            </div>

        </div>

        <div class="actions">

            <button class="btn primary">
                Associar plano
            </button>

            <a
                class="btn secondary"
                href="index.php"
            >
                Cancelar
            </a>

        </div>

    </form>

</div>

<script>

const tutores = <?=json_encode($tutores, JSON_UNESCAPED_UNICODE)?>;

const animais = <?=json_encode($animais, JSON_UNESCAPED_UNICODE)?>;

const pesquisaTutor = document.getElementById('pesquisaTutor');

const tutorId = document.getElementById('tutor_id');

const sugestoesTutores = document.getElementById('sugestoesTutores');

const animalSelect = document.getElementById('animal_id');

const planoSelect = document.getElementById('plano_id');

const quantidadeServicos = document.getElementById('quantidade_servicos');

pesquisaTutor.addEventListener('input', function() {

    const texto = this.value.toLowerCase().trim();

    tutorId.value = '';

    animalSelect.innerHTML = `
        <option value="">
            Primeiro selecione o tutor
        </option>
    `;

    animalSelect.disabled = true;

    if (!texto) {

        sugestoesTutores.style.display = 'none';

        return;

    }

    const resultados = tutores.filter(function(tutor) {

        return tutor.nome.toLowerCase().includes(texto);

    });

    sugestoesTutores.innerHTML = '';

    if (resultados.length === 0) {

        sugestoesTutores.innerHTML = `
            <div style="padding:10px;">
                Nenhum tutor encontrado.
            </div>
        `;

        sugestoesTutores.style.display = 'block';

        return;

    }

    resultados.forEach(function(tutor) {

        const item = document.createElement('div');

        item.textContent = tutor.nome;

        item.style.padding = '10px';

        item.style.cursor = 'pointer';

        item.addEventListener('click', function() {

            pesquisaTutor.value = tutor.nome;

            tutorId.value = tutor.id;

            sugestoesTutores.style.display = 'none';

            carregarAnimais(tutor.id);

        });

        sugestoesTutores.appendChild(item);

    });

    sugestoesTutores.style.display = 'block';

});

function carregarAnimais(idTutor) {

    animalSelect.innerHTML = '';

    const animaisTutor = animais.filter(function(animal) {

        return Number(animal.tutor_id) === Number(idTutor);

    });

    if (animaisTutor.length === 0) {

        animalSelect.innerHTML = `
            <option value="">
                Este tutor não possui animais cadastrados
            </option>
        `;

        animalSelect.disabled = true;

        return;

    }

    const primeiraOpcao = document.createElement('option');

    primeiraOpcao.value = '';

    primeiraOpcao.textContent = 'Selecione o animal';

    animalSelect.appendChild(primeiraOpcao);

    animaisTutor.forEach(function(animal) {

        const option = document.createElement('option');

        option.value = animal.id;

        option.textContent = animal.nome;

        animalSelect.appendChild(option);

    });

    animalSelect.disabled = false;

}

planoSelect.addEventListener('change', function() {

    const opcao = this.options[this.selectedIndex];

    const quantidade = opcao.dataset.servicos || 0;

    quantidadeServicos.value = quantidade;

});

document.addEventListener('click', function(event) {

    if (
        !pesquisaTutor.contains(event.target) &&
        !sugestoesTutores.contains(event.target)
    ) {

        sugestoesTutores.style.display = 'none';

    }

});

</script>

<?php

require __DIR__.'/../includes/footer.php';

?>