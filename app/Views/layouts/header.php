<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Mobile Money') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<?php
  $userType = session()->get('user_type');
  $navClass = $userType === 'operator' ? 'navbar-operator' : ($userType === 'client' ? 'navbar-client' : 'navbar-default');
?>

<nav class="navbar navbar-expand-lg <?= $navClass ?>">
  <div class="container">

    <a class="navbar-brand" href="<?= base_url('/') ?>">
      <div class="brand-icon"><i class="bi bi-phone-fill"></i></div>
      <span>MobiMoney</span>
    </a>

    <button class="navbar-toggler border-0 text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="bi bi-list fs-4"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

        <?php if ($userType === 'client'): ?>

          <li class="nav-item me-2">
            <span class="user-chip">
              <i class="bi bi-person-fill"></i>
              <?= esc(session()->get('numero_telephone')) ?>
            </span>
          </li>

          <?php $route = service('router')->getMatchedRoute(); ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('client/dashboard') ?>">
              <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('client/depot') ?>">
              <i class="bi bi-arrow-down-circle"></i> Depot
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('client/retrait') ?>">
              <i class="bi bi-arrow-up-circle"></i> Retrait
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('client/transfert') ?>">
              <i class="bi bi-send"></i> Transfert
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('client/historique') ?>">
              <i class="bi bi-clock-history"></i> Historique
            </a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn-nav" href="<?= base_url('client/logout') ?>">
              <i class="bi bi-box-arrow-right"></i> Deconnexion
            </a>
          </li>

        <?php elseif ($userType === 'operator'): ?>

          <li class="nav-item me-2">
            <span class="user-chip">
              <i class="bi bi-shield-check"></i>
              <?= esc(session()->get('username') ?? 'Admin') ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('operateur/dashboard') ?>">
              <i class="bi bi-speedometer2"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('operateur/clients') ?>">
              <i class="bi bi-people"></i> Clients
            </a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn-nav" href="<?= base_url('operateur/logout') ?>">
              <i class="bi bi-box-arrow-right"></i> Deconnexion
            </a>
          </li>

        <?php else: ?>

          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('client') ?>">
              <i class="bi bi-person"></i> Espace Client
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('operateur') ?>">
              <i class="bi bi-shield-lock"></i> Espace Operateur
            </a>
          </li>

        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Flash Messages -->
<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="container flash-container">
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
    <span><?= esc($msg) ?></span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>
<?php if ($msg = session()->getFlashdata('error')): ?>
<div class="container flash-container">
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
    <span><?= esc($msg) ?></span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<main>
