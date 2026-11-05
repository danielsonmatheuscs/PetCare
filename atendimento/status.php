
<?php

require_once __DIR__.'/../config/db.php';

$id = (int)($_GET['id'] ?? 0);

$status = $_GET['status'] ?? 'Aguardando';

$permitidos = [
    'Aguardando',
    'Em atendimento',
    'Pronto',
    'Concluído'
];

if ($id <= 0 || !in_array($status, $permitidos, true)) {
    header('Location: index.php');
    exit;
}

try {

    $pdo->beginTransaction();

    // Busca e bloqueia o agendamento.
    $consulta = $pdo->prepare("
        SELECT id, animal_id, servico, status
        FROM agendamentos
        WHERE id = ?
        FOR UPDATE
    ");

    $consulta->execute([$id]);

    $agendamento = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        $pdo->rollBack();
        header('Location: index.php');
        exit;
    }

    $statusAnterior = $agendamento['status'];

    // Registra a utilização somente na primeira conclusão.
    if ($status === 'Concluído' && $statusAnterior !== 'Concluído') {

        // Verifica se esse agendamento já foi registrado.
        $verificarHistorico = $pdo->prepare("
            SELECT id
            FROM planos_utilizacoes
            WHERE agendamento_id = ?
            LIMIT 1
        ");

        $verificarHistorico->execute([$id]);

        $historicoExiste = $verificarHistorico->fetch();

        if (!$historicoExiste) {

            // Busca um plano ativo com serviços disponíveis.
            $consultaPlano = $pdo->prepare("
                SELECT id, servicos_restantes
                FROM planos_animais
                WHERE animal_id = ?
                  AND status = 'Ativo'
                  AND servicos_restantes > 0
                  AND data_inicio <= CURDATE()
                  AND (data_fim IS NULL OR data_fim >= CURDATE())
                ORDER BY data_inicio DESC, id DESC
                LIMIT 1
                FOR UPDATE
            ");

            $consultaPlano->execute([
                $agendamento['animal_id']
            ]);

            $plano = $consultaPlano->fetch(PDO::FETCH_ASSOC);

            if ($plano) {

                $servicosAntes = (int)$plano['servicos_restantes'];

                $servicosDepois = $servicosAntes - 1;

                // Desconta um serviço do plano.
                $atualizarPlano = $pdo->prepare("
                    UPDATE planos_animais
                    SET servicos_restantes = ?
                    WHERE id = ?
                ");

                $atualizarPlano->execute([
                    $servicosDepois,
                    $plano['id']
                ]);

                // Registra a utilização no histórico.
                $registrarHistorico = $pdo->prepare("
                    INSERT INTO planos_utilizacoes (
                        plano_animal_id,
                        agendamento_id,
                        data_utilizacao,
                        servico,
                        servicos_antes,
                        servicos_depois
                    )
                    VALUES (?, ?, NOW(), ?, ?, ?)
                ");

                $registrarHistorico->execute([
                    $plano['id'],
                    $id,
                    $agendamento['servico'],
                    $servicosAntes,
                    $servicosDepois
                ]);

                // Encerra o plano quando acabar os serviços.
                if ($servicosDepois === 0) {

                    $encerrarPlano = $pdo->prepare("
                        UPDATE planos_animais
                        SET status = 'Concluído'
                        WHERE id = ?
                    ");

                    $encerrarPlano->execute([
                        $plano['id']
                    ]);
                }
            }
        }
    }

    // Atualiza o status do agendamento.
    $atualizarAgendamento = $pdo->prepare("
        UPDATE agendamentos
        SET status = ?
        WHERE id = ?
    ");

    $atualizarAgendamento->execute([
        $status,
        $id
    ]);

    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erro ao atualizar atendimento: '.$e->getMessage());

    http_response_code(500);

    exit('Não foi possível atualizar o atendimento. Verifique o log de erros do PHP.');
}

header('Location: index.php');
exit;

?>
