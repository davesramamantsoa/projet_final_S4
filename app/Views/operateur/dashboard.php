<?= view('layouts/header', ['title' => 'Dashboard Operateur — MobiMoney']) ?>

<div class="container-fluid px-4 py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="fw-800 mb-0" style="font-size:1.6rem">Tableau de Bord</h2>
      <p class="text-muted small mb-0">Administration MobiMoney &mdash; <?= date('d M Y') ?></p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('operateur/clients') ?>" class="btn btn-outline-operator btn-sm">
        <i class="bi bi-people me-1"></i>Comptes Clients
      </a>
      <a href="<?= base_url('operateur/creer') ?>" class="btn btn-operator btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Nouvel Operateur
      </a>
    </div>
  </div>

  <?php if (empty($operateurs)): ?>
    <div class="card" style="border:none">
      <div class="empty-state">
        <i class="bi bi-building-add"></i>
        <h5>Aucun operateur configure</h5>
        <p>Commencez par creer votre premier operateur avec ses prefixes.</p>
        <a href="<?= base_url('operateur/creer') ?>" class="btn btn-operator">
          <i class="bi bi-plus-circle me-1"></i>Creer un operateur
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($operateurs as $op):
    $s = $stats[$op['id']] ?? [];
    $gainsTotal = ($s['gains_retrait'] ?? 0) + ($s['gains_transfert'] ?? 0);
  ?>

    <div class="card mb-4" style="border:none;box-shadow:var(--shadow-md)">

      <!-- Header carte opérateur -->
      <div class="op-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-3 d-flex align-items-center justify-content-center"
               style="width:48px;height:48px;background:rgba(255,255,255,.2);font-size:1.4rem">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-700"><?= esc($op['nom_operateur']) ?></h5>
            <span class="badge rounded-pill px-2 py-1 mt-1"
                  style="background:rgba(255,255,255,.2);font-size:.72rem;font-weight:700">
              Prefixe : <?= esc($op['prefixe_operateur']) ?>
            </span>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= base_url('operateur/types/' . $op['id']) ?>"
             class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">
            <i class="bi bi-sliders me-1"></i>Types & Baremes
          </a>
          <a href="<?= base_url('operateur/statistiques/' . $op['id']) ?>"
             class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">
            <i class="bi bi-bar-chart-line me-1"></i>Statistiques
          </a>
        </div>
      </div>

      <!-- Métriques -->
      <div class="card-body p-4">
        <div class="row g-3">

          <div class="col-6 col-md-3">
            <div class="stat-card client">
              <div class="stat-value"><?= number_format($s['total_transactions'] ?? 0) ?></div>
              <div class="stat-label">Transactions</div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="stat-card info">
              <div class="stat-value" style="font-size:1.3rem"><?= number_format($s['volume_total'] ?? 0, 0, ',', ' ') ?></div>
              <div class="stat-label">Volume Ar</div>
            </div>
          </div>

          <div class="col-6 col-md-3">
            <div class="stat-card warning">
              <div class="stat-value" style="font-size:1.3rem"><?= number_format($s['gains_retrait'] ?? 0, 0, ',', ' ') ?></div>
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
        <?php if (!empty($s['par_type'])): ?>
          <div class="mt-4">
            <p class="section-title">Detail par operation</p>
            <div class="row g-3">
              <?php
              $typeDisplay = [
                'depot'     => ['badge-depot',     'bi-arrow-down-circle-fill', 'Depot'],
                'retrait'   => ['badge-retrait',   'bi-arrow-up-circle-fill',   'Retrait'],
                'transfert' => ['badge-transfert', 'bi-send-fill',              'Transfert'],
              ];
              foreach ($s['par_type'] as $type => $ts):
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
        <?php endif; ?>
      </div>
    </div>

  <?php endforeach; ?>

</div>

