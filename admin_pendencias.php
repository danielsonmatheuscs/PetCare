<?php
require_once __DIR__.'/config/auth.php';petcare_require_admin();require_once __DIR__.'/config/workflow.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 petcare_check_csrf();$id=(int)($_POST['id']??0);
 try{
  $pdo->beginTransaction();$q=$pdo->prepare('SELECT * FROM atendimento_fechamentos WHERE agendamento_id=? FOR UPDATE');$q->execute([$id]);$f=$q->fetch();if(!$f)throw new RuntimeException('Fechamento não encontrado.');
  $value=pc_money($_POST['valor']??'');if($value<=0||$value>(float)$f['valor_pendente'])throw new RuntimeException('Valor maior que o saldo pendente ou inválido.');
  $forma=(string)($_POST['forma']??'');if(!in_array($forma,['Pix','Dinheiro','Cartão','Transferência','Outro'],true))throw new RuntimeException('Forma de pagamento inválida.');
  $fid=(int)($_SESSION['petcare_user']['id']??0);
  $pdo->prepare('UPDATE atendimento_fechamentos SET valor_recebido=valor_recebido+?,valor_pendente=valor_pendente-? WHERE agendamento_id=?')->execute([$value,$value,$id]);
  // A tabela financeiro original aceita somente uma entrada por agendamento.
  // Para pagamentos parciais subsequentes, o lançamento financeiro não usa esse campo único;
  // o identificador permanece na descrição e em atendimento_recebimentos.
  $pdo->prepare('INSERT INTO atendimento_recebimentos(agendamento_id,funcionario_id,valor,forma_pagamento) VALUES(?,?,?,?)')->execute([$id,$fid,$value,$forma]);
  $pdo->prepare("INSERT INTO financeiro(tipo,categoria,descricao,valor,forma_pagamento,agendamento_id) VALUES('entrada','Recebimento de pendência',?,?,?,NULL)")->execute(['Agendamento #'.$id,$value,$forma]);
  $pdo->commit();petcare_flash('Recebimento registrado.','success');header('Location:admin_pendencias.php');exit;
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();petcare_back($e instanceof RuntimeException?$e->getMessage():'Erro ao registrar pagamento.');}
}
$rows=$pdo->query("SELECT af.*,a.servico,an.nome animal FROM atendimento_fechamentos af JOIN agendamentos a ON a.id=af.agendamento_id JOIN animais an ON an.id=a.animal_id WHERE af.valor_pendente>0 ORDER BY af.concluido_em DESC")->fetchAll();$pageTitle='Pendências financeiras';require __DIR__.'/includes/header.php';?>
<header class="topbar"><div><h1>Contas a receber</h1><p>Registre pagamentos posteriores sem alterar o histórico do atendimento.</p></div></header>
<div class="card table-wrap"><table class="table"><thead><tr><th>Animal / Serviço</th><th>Saldo pendente</th><th>Receber</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=htmlspecialchars($r['animal'].' — '.$r['servico'])?></td><td>R$ <?=number_format($r['valor_pendente'],2,',','.')?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(petcare_csrf_token())?>"><input type="hidden" name="id" value="<?=$r['agendamento_id']?>"><input type="number" name="valor" min="0.01" max="<?=$r['valor_pendente']?>" step="0.01" required placeholder="Valor recebido"><select name="forma"><option>Pix</option><option>Dinheiro</option><option>Cartão</option><option>Transferência</option><option>Outro</option></select><button class="btn primary">Registrar</button></form></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="3">Nenhuma pendência registrada neste módulo.</td></tr><?php endif;?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php';?>
