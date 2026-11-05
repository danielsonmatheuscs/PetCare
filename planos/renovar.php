
<?php

require_once __DIR__.'/../config/db.php';

$pageTitle = 'Renovar plano';

$erro = '';
$sucesso = '';

$tutores = $pdo->query("
    SELECT id, nome
    FROM tutores
    ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

$planos = $pdo->query("
    SELECT id, nome, quantidade_servicos, valor
    FROM planos
    WHERE ativo = 1
    ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

$animais = $pdo->query("
    SELECT
        a.id,
        a.nome,
        a.tutor_id,
        t.nome AS tutor
    FROM animais a
    INNER JOIN tutores t ON t.id = a.tutor_id
    ORDER BY a.nome
")->fetchAll(PDO::FETCH_ASSOC);

$animalSelecionado = (int)($_POST['animal_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $animalId = (int)($_POST['animal_id'] ?? 0);
    $planoId = (int)($_POST['plano_id'] ?? 0);
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';
    $observacoes = trim($_POST['observacoes'] ?? '');

    try {

        if ($animalId <= 0 || $planoId <= 0 || $dataInicio === '') {
            throw new Exception('Preencha os campos obrigatórios.');
        }

        if ($dataFim !== '' && $dataFim < $dataInicio) {
            throw new Exception('A data final não pode ser anterior à data inicial.');
        }

        $pdo->beginTransaction();

        // Verifica o animal e seu tutor.
        $consultaAnimal = $pdo->prepare("
            SELECT id, tutor_id
            FROM animais
            WHERE id = ?
        ");

        $consultaAnimal->execute([$animalId]);

        $animal = $consultaAnimal->fetch(PDO::FETCH_ASSOC);

        if (!$animal) {
            throw new Exception('Animal não encontrado.');
        }

        // Busca o plano escolhido.
        $consultaPlano = $pdo->prepare("
            SELECT id, quantidade_servicos
            FROM planos
            WHERE id = ?
              AND ativo = 1
        ");

        $consultaPlano->execute([$planoId]);

        $plano = $consultaPlano->fetch(PDO::FETCH_ASSOC);

        if (!$plano) {
            throw new Exception('Plano inválido ou inativo.');
        }

        // Cria uma nova contratação.
        $inserir = $pdo->prepare("
            INSERT INTO planos_animais (
                plano_id,
                animal_id,
                tutor_id,
                data_inicio,
                data_fim,
                servicos_restantes,
                status,
                observacoes
            )
            VALUES (?, ?, ?, ?, ?, ?, 'Ativo', ?)
        ");

        $inserir->execute([
            $planoId,
            $animalId,
            $animal['tutor_id'],
            $dataInicio,
            $dataFim !== '' ? $dataFim : null,
            $plano['quantidade_servicos'],
            $observacoes
        ]);

        $pdo->commit();

        $sucesso = 'Plano renovado com sucesso!';

        $animalSelecionado = $animalId;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erro = $e instanceof PDOException
            ? 'Não foi possível renovar o plano. Verifique os dados e tente novamente.'
            : $e->getMessage();

        error_log('Erro ao renovar plano: '.$e->getMessage());
    }
}

require __DIR__.'/../includes/header.php';

?>

<header class="topbar">

    <div>
        <h1>Renovar plano</h1>
        <p>Crie uma nova contratação preservando o histórico anterior.</p>
    </div>

    <div class="actions-inline">
        <a class="btn secondary" href="index.php">
            Voltar aos planos
        </a>
    </div>

</header>

<div class="card">

    <?php if ($erro !== ''): ?>

        <div class="alert danger">
            <?=htmlspecialchars($erro)?>
        </div>

    <?php endif; ?>

    <?php if ($sucesso !== ''): ?>

        <div class="alert success">
            <?=htmlspecialchars($sucesso)?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:15px;">

            <div>
                <label for="animal_id">Animal *</label>

                <select id="animal_id" name="animal_id" required>

                    <option value="">Selecione o animal</option>

                    <?php foreach ($animais as $a): ?>

                        <option
                            value="<?=$a['id']?>"
                            <?=$animalSelecionado === (int)$a['id'] ? 'selected' : ''?>
                        >
                            <?=htmlspecialchars($a['nome'].' - '.$a['tutor'])?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <div>
                <label for="plano_id">Novo plano *</label>

                <select id="plano_id" name="plano_id" required>

                    <option value="">Selecione o plano</option>

                    <?php foreach ($planos as $p): ?>

                        <option
                            value="<?=$p['id']?>"
                            <?=((int)($_POST['plano_id'] ?? 0) === (int)$p['id']) ? 'selected' : ''?>
                        >
                            <?=htmlspecialchars($p['nome'])?>
                            - <?=$p['quantidade_servicos']?> serviços
                            - R$ <?=number_format($p['valor'], 2, ',', '.')?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <div>
                <label for="data_inicio">Data de início *</label>

                <input
                    type="date"
                    id="data_inicio"
                    name="data_inicio"
                    value="<?=htmlspecialchars($_POST['data_inicio'] ?? date('Y-m-d'))?>"
                    required
                >
            </div>

            <div>
                <label for="data_fim">Data de término</label>

                <input
                    type="date"
                    id="data_fim"
                    name="data_fim"
                    value="<?=htmlspecialchars($_POST['data_fim'] ?? '')?>"
                >
            </div>

        </div>

        <div style="margin-top:15px;">

            <label for="observacoes">Observações</label>

            <textarea
                id="observacoes"
                name="observacoes"
                rows="3"
            ><?=htmlspecialchars($_POST['observacoes'] ?? '')?></textarea>

        </div>

        <div class="actions-inline" style="margin-top:20px;">

            <button type="submit" class="btn primary">
                Renovar plano
            </button>

            <a class="btn secondary" href="index.php">
                Cancelar
            </a>

        </div>

    </form>

</div>

<?php

require __DIR__.'/../includes/footer.php';

?>
