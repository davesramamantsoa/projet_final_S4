<?= view('layouts/header', ['title' => 'Dashboard Opérateur — MobiMoney']) ?>

<div class="container-fluid px-4 py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="fw-800 mb-0" style="font-size:1.6rem"><?= esc($operateur['nom_operateur']) ?></h2>
      <p class="text-muted small mb-0">
        Préfixes: <?= esc($operateur['prefixe_operateur']) ?> &mdash; <?= date('d M Y') ?>
      </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= base_url('operateur/types/' . $operateur['id']) ?>" class="btn btn-outline-operator btn-sm">
        <i class="bi bi-sliders me-1"></i>Types & Barèmes
      </a>
      <a href="<?= base_url('operateur/statistiques/' . $operateur['id']) ?>" class="btn btn-success btn-sm">
        <i class="bi bi-bar-chart-line me-1"></i>Statistiques
      </a>
    </div>
  </div>

  <?php
    $gainsTotal = ($stats['gains_retrait'] ?? 0) + ($stats['gains_transfert'] ?? 0);
  ?>

  <!-- KPIs -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card client">
        <div class="stat-value"><?= number_format($stats['total_transactions'] ?? 0) ?></div>
        <div class="stat-label">Transactions</div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card info">
        <div class="stat-value" style="font-size:1.3rem"><?= number_format($stats['volume_total'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Volume Ar</div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card warning">
        <div class="stat-value" style="font-size:1.3rem"><?= number_format($stats['gains_retrait'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Gains Retrait Ar</div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card success">
        <div class="stat-value" style="font-size:1.3rem"><?= number_format($gainsTotal, 0, ',', ' ') ?></div>
        <div class="stat-label">Total Gains Ar</div>
      </div>
    </div>
  </div>

  <!-- Breakdown par type -->
  <?php if (!empty($stats['par_type'])): ?>
    <div class="card mb-4" style="border:none;box-shadow:var(--shadow-sm)">
      <div class="card-header fw-700">Détail par opération</div>
      <div class="card-body">
        <div class="row g-3">
          <?php
          $typeDisplay = [
            'depot'     => ['badge-depot',     'bi-arrow-down-circle-fill', 'Dépôt'],
            'retrait'   => ['badge-retrait',   'bi-arrow-up-circle-fill',   'Retrait'],
            'transfert' => ['badge-transfert', 'bi-send-fill',              'Transfert'],
          ];
          foreach ($stats['par_type'] as $type => $ts):
            $cfg = $typeDisplay[$type] ?? ['badge-secondary','bi-circle',ucfirst($type)];
          ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3" style="background:#FAFAFA;border:1px solid var(--border)">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="badge <?= $cfg[0] ?>"><i class="bi <?= $cfg[1] ?>"></i> <?= $cfg[2] ?></span>
                  <span class="fw-700 small"><?= number_format($ts['count']) ?> tx</span>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                  <span>Volume</span>
                  <strong class="text-dark"><?= number_format($ts['volume'], 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-1">
                  <span>Frais</span>
                  <strong class="text-dark"><?= number_format($ts['frais'], 0, ',', ' ') ?> Ar</strong>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<?= view('layouts/footer') ?>
