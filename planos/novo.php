<?php
require_once __DIR__.'/../config/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("INSERT INTO planos(nome,descricao,valor,quantidade_servicos,ativo) VALUES(?,?,?,?,?)");
    $s->execute([$_POST['nome'],$_POST['descricao'],$_POST['valor'],$_POST['quantidade_servicos'],$_POST['ativo']]);
    header('Location:index.php');
    exit;
}
$pageTitle='Novo plano';
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Novo plano</h1>
        <p>Cadastre um plano comercial.</p>
    </div>
</header>
<div class="card form-card">
    <form method="post">
        <div class="form-grid">
            <div class="field full">
                <label>Nome *</label>
                <input name="nome" required>
            </div>
            <div class="field full">
                <label>Descrição</label>
                <textarea name="descricao"></textarea>
            </div>
            <div class="field">
                <label>Valor mensal *</label>
                <input type="number" step="0.01" name="valor" required>
            </div>
            <div class="field">
                <label>Quantidade de serviços</label>
                <input type="number" name="quantidade_servicos" value="1" min="1">
            </div>
            <div class="field">
                <label>Ativo</label>
                <select name="ativo">
                    <option value="1">Sim</option>
                    <option value="0">Não</option>
                </select>
            </div>
        </div>
        <div class="actions">
            <button class="btn primary">Salvar</button>
            <a class="btn secondary" href="index.php">Cancelar</a>
        </div>
    </form>
</div>
<?php
require __DIR__.'/../includes/footer.php';
?>