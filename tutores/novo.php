<?php
require_once __DIR__.'/../config/db.php';
$pageTitle='Novo tutor';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("INSERT INTO tutores(nome,cpf,telefone,email,endereco) VALUES(?,?,?,?,?)");
    $s->execute([$_POST['nome'],$_POST['cpf'],$_POST['telefone'],$_POST['email'],$_POST['endereco']]);
    header('Location: index.php');
    exit;
}
require __DIR__.'/../includes/header.php';
?>
<header class="topbar">
    <div>
        <h1>Novo tutor</h1>
        <p>Cadastre o responsável pelo animal.</p>
    </div>
</header>
<div class="card form-card">
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label>Nome *</label>
                <input name="nome" required>
            </div>
            <div class="field">
                <label>CPF</label>
                <input name="cpf">
            </div>
            <div class="field">
                <label>Telefone *</label>
                <input name="telefone" required>
            </div>
            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email">
            </div>
            <div class="field full">
                <label>Endereço</label>
                <input name="endereco">
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