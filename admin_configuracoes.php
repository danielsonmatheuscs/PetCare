<?php
require_once __DIR__.'/config/auth.php';petcare_require_admin();require_once __DIR__.'/config/workflow.php';
$opts=['transporte'=>'Busca e entrega de animais','confirmar_entrega'=>'Exigir confirmação de entrega','financeiro_obrigatorio'=>'Exigir registro financeiro no encerramento','adicionais'=>'Permitir serviços adicionais','planos'=>'Habilitar planos','controle_caixa'=>'Controle de caixa','etapas_ativas'=>'Acompanhar execução por etapas','etapas_exigir_responsavel'=>'Exigir funcionário autorizado para executar etapas'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 petcare_check_csrf();foreach($opts as $key=>$label){$sql=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)==='pgsql' ? 'INSERT INTO configuracoes_estabelecimento(chave,valor) VALUES(?,?) ON CONFLICT (chave) DO UPDATE SET valor=EXCLUDED.valor' : 'INSERT INTO configuracoes_estabelecimento(chave,valor) VALUES(?,?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)';$q=$pdo->prepare($sql);$q->execute([$key,isset($_POST[$key])?'1':'0']);}petcare_flash('Configurações atualizadas.','success');header('Location:admin_configuracoes.php');exit;
}
$pageTitle='Configurações do estabelecimento';require __DIR__.'/includes/header.php';?>
<header class="topbar"><div><h1>Configurações do estabelecimento</h1><p>Personalize as etapas utilizadas pela empresa.</p></div></header>
<div class="card form-card"><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(petcare_csrf_token())?>">
<?php foreach($opts as $key=>$label):?><p><label><input type="checkbox" name="<?=$key?>" value="1" <?=pc_setting($pdo,$key)==='1'?'checked':''?>> <?=htmlspecialchars($label)?></label></p><?php endforeach;?><button class="btn primary">Salvar configurações</button></form></div>
<p>As verificações de autorização, integridade financeira e proteção de dados permanecem obrigatórias.</p>
<?php require __DIR__.'/includes/footer.php';?>
