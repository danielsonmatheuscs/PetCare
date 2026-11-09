<?php
require_once __DIR__.'/db.php';
function pc_setting(PDO $pdo,string $key,string $fallback='0'):string {
 $q=$pdo->prepare('SELECT valor FROM configuracoes_estabelecimento WHERE chave=?');$q->execute([$key]);return (string)($q->fetchColumn()?:$fallback);
}
function pc_staff(PDO $pdo,string $code,string $pin,string $permission,?int $serviceId=null):array {
 if($code==='' || $pin==='') throw new RuntimeException('Informe seu código e PIN.');
 $q=$pdo->prepare('SELECT id,nome,pin_hash FROM funcionarios WHERE codigo=? AND ativo=1');$q->execute([$code]);$f=$q->fetch();
 if(!$f || !password_verify($pin,(string)$f['pin_hash'])) throw new RuntimeException('Código ou PIN inválido.');
 $q=$pdo->prepare('SELECT 1 FROM funcionario_permissoes WHERE funcionario_id=? AND permissao=?');$q->execute([$f['id'],$permission]);
 if(!$q->fetchColumn()) throw new RuntimeException('Funcionário não autorizado para esta operação.');
 if($serviceId!==null){$q=$pdo->prepare('SELECT 1 FROM funcionario_servicos WHERE funcionario_id=? AND servico_id=?');$q->execute([$f['id'],$serviceId]);if(!$q->fetchColumn())throw new RuntimeException('Funcionário não autorizado para este serviço.');}
 return $f;
}
// Identificação por etapa é opcional; a sessão autenticada continua obrigatória.
function pc_actor(PDO $pdo,string $code,string $pin,string $permission,?int $serviceId=null):?int {
 if(pc_setting($pdo,'etapas_exigir_responsavel')==='1') {
  $f=pc_staff($pdo,$code,$pin,$permission,$serviceId);
  return (int)$f['id'];
 }
 $user=$_SESSION['petcare_user']??[];
 if(($user['role']??'')==='admin') return null;
 if(($user['role']??'')!=='funcionario' || empty($user['id'])) throw new RuntimeException('Sessão inválida. Entre novamente.');
 $id=(int)$user['id'];
 $q=$pdo->prepare('SELECT id FROM funcionarios WHERE id=? AND ativo=1');$q->execute([$id]);
 if(!$q->fetchColumn()) throw new RuntimeException('Funcionário inativo.');
 $q=$pdo->prepare('SELECT 1 FROM funcionario_permissoes WHERE funcionario_id=? AND permissao=?');$q->execute([$id,$permission]);
 if(!$q->fetchColumn()) throw new RuntimeException('Sem permissão para esta operação.');
 if($serviceId!==null){
  $q=$pdo->prepare('SELECT 1 FROM funcionario_servicos WHERE funcionario_id=? AND servico_id=?');$q->execute([$id,$serviceId]);
  if(!$q->fetchColumn())throw new RuntimeException('Sem autorização para este serviço.');
 }
 return $id;
}
function pc_event(PDO $pdo,int $id,?int $fid,string $event,string $details=''):void {
 $pdo->prepare('INSERT INTO atendimento_eventos(agendamento_id,funcionario_id,evento,detalhes) VALUES(?,?,?,?)')->execute([$id,$fid,$event,$details]);
}
function pc_money($value):float {if(!is_numeric($value))throw new RuntimeException('Valor financeiro inválido.');$v=round((float)$value,2);if($v<0 || $v>9999999)throw new RuntimeException('Valor financeiro inválido.');return $v;}
