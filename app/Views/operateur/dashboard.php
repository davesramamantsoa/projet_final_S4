<?= view('layouts/header', ['title' => 'Dashboard Opérateur — MobiMoney']) ?>

<div class="container-fluid px-4 py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="fw-800 mb-0" style="font-size:1.6rem">Tableau de Bord</h2>
      <p class="text-muted small mb-0">Mon Opérateur Mobile Money &mdash; <?= date('d M Y') ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= base_url('operateur/config') ?>" class="btn btn-outline-operator btn-sm">
        <i class="bi bi-gear me-1"></i>Configuration
      </a>
      <a href="<?= base_url('operateur/clients') ?>" class="btn btn-outline-operator btn-sm">
        <i class="bi bi-people me-1"></i>Mes Clients
      </a>
    </div>
  </div>

  <?php 
    // Mon opérateur (le premier dans la liste)
    $monOp = $operateurs[0] ?? null;
    if (!$monOp) {
      echo '<div class="alert alert-warning">Veuillez créer votre opérateur</div>';
      echo view('layouts/footer');
      return;
    }
    $s = $stats[$monOp['id']] ?? [];
    $gainsTotal = ($s['gains_retrait'] ?? 0) + ($s['gains_transfert'] ?? 0) + ($s['commissions_recues'] ?? 0);
  ?>

  <!-- Info Mon Opérateur -->
  <div class="card mb-4" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1);background:linear-gradient(135deg,#0891b2,#06b6d4)">
    <div class="card-body p-4">
      <div class="d-flex align-items-center justify-content-between text-white">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-3 d-flex align-items-center justify-content-center"
               style="width:56px;height:56px;background:rgba(255,255,255,.2);font-size:1.6rem">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <h4 class="mb-1 fw-700"><?= esc($monOp['nom_operateur']) ?></h4>
            <div class="d-flex gap-2 flex-wrap">
              <span class="badge px-2 py-1" style="background:rgba(255,255,255,.25);font-size:.8rem">
                Préfixes : <?= esc($monOp['prefixe_operateur']) ?>
              </span>
            </div>
          </div>
        </div>
        <div class="text-end">
          <a href="<?= base_url('operateur/editer/' . $monOp['id']) ?>" class="btn btn-sm" 
             style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3)">
            <i class="bi bi-pencil me-1"></i>Modifier
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs Globaux -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#f0f9ff;border:1px solid #bae6fd">
        <div class="stat-value" style="color:#0369a1"><?= number_format($s['total_transactions'] ?? 0) ?></div>
        <div class="stat-label">Transactions</div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#ecfdf5;border:1px solid #a7f3d0">
        <div class="stat-value" style="font-size:1.3rem;color:#047857"><?= number_format($s['volume_total'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Volume Total (Ar)</div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#fefce8;border:1px solid #fde047">
        <div class="stat-value" style="font-size:1.3rem;color:#a16207"><?= number_format($s['gains_retrait'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Gains Retrait</div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;border:none">
        <div class="stat-value" style="color:#fff;font-size:1.4rem"><?= number_format($gainsTotal, 0, ',', ' ') ?></div>
        <div class="stat-label" style="color:rgba(255,255,255,.8)">Total Gains (Ar)</div>
      </div>
    </div>
  </div>

  <!-- Détails par opération -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card h-100" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-header fw-700" style="background:#0891b2;color:#fff">
          💰 Situation des Gains
        </div>
        <div class="card-body">
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:#a16207"><i class="bi bi-cash-coin me-2"></i>Gains Retrait</span>
              <span class="fw-bold fs-5" style="color:#a16207"><?= number_format($s['gains_retrait'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
          </div>
          
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:#065f46"><i class="bi bi-arrow-left-right me-2"></i>Transfert (Même opérateur)</span>
              <span class="fw-bold fs-5" style="color:#065f46"><?= number_format($s['gains_transfert_meme_op'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
          </div>
          
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:#047857"><i class="bi bi-send-check me-2"></i>Transfert (Autres opérateurs)</span>
              <span class="fw-bold fs-5" style="color:#047857"><?= number_format($s['gains_transfert_autre_op'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
          </div>
          
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold" style="color:#1e40af"><i class="bi bi-cash-stack me-2"></i>Commissions reçues</span>
              <span class="fw-bold fs-5" style="color:#1e40af"><?= number_format($s['commissions_recues'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
          </div>
          
          <div class="d-flex justify-content-between mt-3 pt-3 px-3 py-2 rounded" style="background:linear-gradient(135deg,#ecfeff,#cffafe)">
            <span class="fw-bold fs-5" style="color:#0e7490"><i class="bi bi-calculator me-2"></i>Total</span>
            <span class="fw-bold fs-4" style="color:#0e7490"><?= number_format($gainsTotal, 0, ',', ' ') ?> Ar</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-header fw-700" style="background:#fbbf24;color:#78350f">
          📤 Montants à Envoyer
        </div>
        <div class="card-body">
          <?php if (empty($s['montants_a_envoyer'])): ?>
            <div class="text-center py-5 text-muted">
              <i class="bi bi-check-circle fs-1 text-success"></i>
              <p class="mt-2 mb-0">Aucun montant à envoyer</p>
            </div>
          <?php else: ?>
            <?php $totalAEnvoyer = array_sum($s['montants_a_envoyer']); ?>
            <div class="list-group list-group-flush">
              <?php foreach ($s['montants_a_envoyer'] as $opName => $montant): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                  <div>
                    <span class="fw-semibold fs-6"><i class="bi bi-building me-2" style="color:#d97706"></i><?= esc($opName) ?></span>
                    <div class="small text-muted mt-1">
                      <?= $totalAEnvoyer > 0 ? number_format($montant / $totalAEnvoyer * 100, 1) : 0 ?>% du total
                    </div>
                  </div>
                  <span class="fw-bold fs-6" style="color:#92400e"><?= number_format($montant, 0, ',', ' ') ?> Ar</span>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between mt-3 pt-3 px-3 py-2 rounded" style="background:#fef3c7;border:2px solid #fbbf24">
              <span class="fw-bold" style="color:#78350f">Total à envoyer</span>
              <span class="fw-bold fs-5" style="color:#78350f"><?= number_format($totalAEnvoyer, 0, ',', ' ') ?> Ar</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Liens rapides -->
  <div class="row g-3">
    <div class="col-md-4">
      <a href="<?= base_url('operateur/types/' . $monOp['id']) ?>" class="card text-decoration-none" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-body text-center py-4">
          <i class="bi bi-sliders fs-1 text-operator"></i>
          <h6 class="mt-2 mb-0">Gérer les Barèmes</h6>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= base_url('operateur/statistiques/' . $monOp['id']) ?>" class="card text-decoration-none" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-body text-center py-4">
          <i class="bi bi-bar-chart-line fs-1 text-success"></i>
          <h6 class="mt-2 mb-0">Statistiques Détaillées</h6>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= base_url('operateur/clients') ?>" class="card text-decoration-none" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-body text-center py-4">
          <i class="bi bi-people fs-1 text-primary"></i>
          <h6 class="mt-2 mb-0">Mes Clients</h6>
        </div>
      </a>
    </div>
  </div>

</div>

<?= view('layouts/footer') ?>
