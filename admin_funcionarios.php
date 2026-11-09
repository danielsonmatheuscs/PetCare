<?php
require_once __DIR__ . '/config/auth.php';
petcare_require_admin();
require_once __DIR__ . '/config/workflow.php';
$pageTitle='Administração · Funcionários';
$erro='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  petcare_check_csrf();
  $acao=$_POST['acao']??'';
  if($acao==='permissoes'){
    $fid=(int)($_POST['id']??0);$check=$pdo->prepare('SELECT id FROM funcionarios WHERE id=?');$check->execute([$fid]);if(!$check->fetchColumn())petcare_back('Funcionário não encontrado.');
    $valid=['iniciar','pronto','buscar','sair_entrega','entregar','receber','adicional','observacao'];
    $pdo->beginTransaction();try {
      $pdo->prepare('DELETE FROM funcionario_permissoes WHERE funcionario_id=?')->execute([$fid]);
      foreach((array)($_POST['permissoes']??[]) as $p){if(in_array($p,$valid,true))$pdo->prepare('INSERT INTO funcionario_permissoes(funcionario_id,permissao) VALUES(?,?)')->execute([$fid,$p]);}
      $pdo->prepare('DELETE FROM funcionario_servicos WHERE funcionario_id=?')->execute([$fid]);
      foreach(array_unique(array_map('intval',(array)($_POST['servicos']??[]))) as $sid){$q=$pdo->prepare('SELECT id FROM servicos WHERE id=?');$q->execute([$sid]);if($q->fetchColumn())$pdo->prepare('INSERT INTO funcionario_servicos(funcionario_id,servico_id) VALUES(?,?)')->execute([$fid,$sid]);}
      $pdo->commit();petcare_flash('Permissões salvas.','success');header('Location:admin_funcionarios.php');exit;
    }catch(Throwable $e){$pdo->rollBack();petcare_back('Erro ao salvar permissões.');}
  }elseif($acao==='salvar'){
    $id=(int)($_POST['id']??0);$nome=trim($_POST['nome']??'');$codigo=trim($_POST['codigo']??'');$pin=trim($_POST['pin']??'');$ativo=isset($_POST['ativo'])?1:0;
    if($nome===''){$erro='Informe o nome do funcionário.';}
    else{
      try{
        if($id>0){
          if($pin!==''){$hash=password_hash($pin,PASSWORD_DEFAULT);$q=$pdo->prepare('UPDATE funcionarios SET nome=?,codigo=?,pin_hash=?,ativo=? WHERE id=?');$q->execute([$nome,$codigo?:null,$hash,$ativo,$id]);}
          else{$q=$pdo->prepare('UPDATE funcionarios SET nome=?,codigo=?,ativo=? WHERE id=?');$q->execute([$nome,$codigo?:null,$ativo,$id]);}
        }else{
          if($pin===''){$erro='Defina um PIN para o novo funcionário.';}else{$hash=password_hash($pin,PASSWORD_DEFAULT);$q=$pdo->prepare('INSERT INTO funcionarios(nome,codigo,pin_hash,ativo) VALUES(?,?,?,?)');$q->execute([$nome,$codigo?:null,$hash,$ativo]);}
        }
      }catch(Throwable $e){$erro='Não foi possível salvar. Verifique se o código já está cadastrado.';error_log($e->getMessage());}
    }
  }elseif($acao==='excluir'){
    $id=(int)($_POST['id']??0);if($id>0){$q=$pdo->prepare('UPDATE funcionarios SET ativo=0 WHERE id=?');$q->execute([$id]);}
  }
  if($erro===''){header('Location: admin_funcionarios.php');exit;}
}
$edit=null;if(isset($_GET['editar'])){$q=$pdo->prepare('SELECT id,nome,codigo,ativo FROM funcionarios WHERE id=?');$q->execute([(int)$_GET['editar']]);$edit=$q->fetch();}
$rows=$pdo->query('SELECT id,nome,codigo,ativo,created_at FROM funcionarios ORDER BY ativo DESC,nome')->fetchAll();
$servicos=$pdo->query('SELECT id,nome FROM servicos WHERE ativo=1 ORDER BY nome')->fetchAll();
$perms=$pdo->query('SELECT funcionario_id,permissao FROM funcionario_permissoes')->fetchAll();$permMap=[];foreach($perms as $p)$permMap[$p['funcionario_id']][]=$p['permissao'];
$staffServices=$pdo->query('SELECT funcionario_id,servico_id FROM funcionario_servicos')->fetchAll();$svcMap=[];foreach($staffServices as $p)$svcMap[$p['funcionario_id']][]=(int)$p['servico_id'];
$permissionLabels=['iniciar'=>'Iniciar serviço','pronto'=>'Marcar serviço pronto','buscar'=>'Buscar animal','sair_entrega'=>'Sair para entrega','entregar'=>'Confirmar entrega','receber'=>'Confirmar recebimento e encerrar','adicional'=>'Adicionar serviço autorizado','observacao'=>'Registrar observações'];
require __DIR__.'/includes/header.php';
?>
<header class="topbar"><div><h1>Administração</h1><p>Gerencie a equipe responsável pelos atendimentos.</p></div></header>
<?php if($erro):?><div class="card" style="color:#b42318;margin-bottom:16px"><?=htmlspecialchars($erro)?></div><?php endif;?>
<div class="card form-card"><h2><?= $edit?'Editar funcionário':'Cadastrar funcionário'?></h2><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(petcare_csrf_token())?>"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?=$edit['id']??0?>"><div class="form-grid"><div class="field"><label>Nome completo *</label><input name="nome" required value="<?=htmlspecialchars($edit['nome']??'')?>"></div><div class="field"><label>Código de identificação (opcional)</label><input name="codigo" value="<?=htmlspecialchars($edit['codigo']??'')?>" placeholder="Ex.: BANH01"></div><div class="field"><label>PIN (4 a 8 dígitos) <?=$edit?'— deixe vazio para manter':'*'?></label><input name="pin" type="password" inputmode="numeric" minlength="4" maxlength="8" pattern="[0-9]{4,8}" <?=$edit?'':'required'?> autocomplete="new-password"></div><div class="field"><label><input type="checkbox" name="ativo" value="1" <?=(!isset($edit)||!empty($edit['ativo']))?'checked':''?>> Funcionário ativo</label></div></div><div class="actions"><button class="btn primary" type="submit">Salvar funcionário</button><?php if($edit):?><a class="btn secondary" href="admin_funcionarios.php">Cancelar edição</a><?php endif;?></div></form></div>
<div class="card table-wrap" style="margin-top:20px"><table class="table"><thead><tr><th>Funcionário</th><th>Código</th><th>Status</th><th>Ações</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=htmlspecialchars($r['nome'])?></td><td><?=htmlspecialchars($r['codigo']??'—')?></td><td><?=$r['ativo']?'Ativo':'Inativo'?></td><td class="actions-inline"><a class="btn secondary" href="admin_funcionarios.php?editar=<?=$r['id']?>">Editar</a><?php if($r['ativo']):?><form method="post" style="display:inline" onsubmit="return confirm('Desativar este funcionário?')"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(petcare_csrf_token())?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn danger" type="submit">Desativar</button></form><?php endif;?><details><summary>Serviços e permissões</summary><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(petcare_csrf_token())?>"><input type="hidden" name="acao" value="permissoes"><input type="hidden" name="id" value="<?=$r['id']?>"><p><strong>Operações autorizadas</strong></p><?php foreach($permissionLabels as $key=>$label):?><label style="display:block"><input type="checkbox" name="permissoes[]" value="<?=$key?>" <?=in_array($key,$permMap[$r['id']]??[],true)?'checked':''?>> <?=htmlspecialchars($label)?></label><?php endforeach;?><p><strong>Serviços que pode executar</strong></p><?php foreach($servicos as $sv):?><label style="display:block"><input type="checkbox" name="servicos[]" value="<?=$sv['id']?>" <?=in_array((int)$sv['id'],$svcMap[$r['id']]??[],true)?'checked':''?>> <?=htmlspecialchars($sv['nome'])?></label><?php endforeach;?><button class="btn primary">Salvar permissões</button></form></details></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="4" class="empty">Nenhum funcionário cadastrado.</td></tr><?php endif;?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php';?>
