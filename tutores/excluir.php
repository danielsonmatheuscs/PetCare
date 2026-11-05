<?php
require_once __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare("DELETE FROM tutores WHERE id=?");
$s->execute([$id]);
header('Location:index.php');
exit;
?>