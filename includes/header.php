<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetCare | <?= htmlspecialchars($pageTitle ?? 'Sistema') ?></title>
<link rel="stylesheet" href="/PetCare/css/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
  <div class="brand"><span>🐾</span><div><strong>PetCare</strong><small>Banho & Tosa</small></div></div>
  <nav>
    <a class="<?= $current === 'index.php' ? 'active' : '' ?>" href="/PetCare/index.php">🏠 Dashboard</a>
    <a class="<?= str_contains($_SERVER['PHP_SELF'],'tutores') ? 'active' : '' ?>" href="/PetCare/tutores/index.php">👤 Tutores</a>
    <a class="<?= str_contains($_SERVER['PHP_SELF'],'animais') ? 'active' : '' ?>" href="/PetCare/animais/index.php">🐶 Animais</a>
    <a class="<?= str_contains($_SERVER['PHP_SELF'],'agendamentos') ? 'active' : '' ?>" href="/PetCare/agendamentos/index.php">📅 Agendamentos</a>
    <a class="<?= str_contains($_SERVER['PHP_SELF'],'atendimento') ? 'active' : '' ?>" href="/PetCare/atendimento/index.php">🛁 Atendimento</a>
    <a class="<?= str_contains($_SERVER['PHP_SELF'],'planos') ? 'active' : '' ?>" href="/PetCare/planos/index.php">💳 Planos</a>
  </nav>
  <div class="sidebar-bottom">Sistema acadêmico<br><small>Projeto PetCare</small></div>
</aside>
<main class="main">
