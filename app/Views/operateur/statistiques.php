<?= view('layouts/header', ['title' => 'Statistiques — MobiMoney']) ?>

<div class="container-fluid px-4 py-4">

  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
      <h2 class="fw-800 mb-0" style="font-size:1.5rem">Statistiques</h2>
      <p class="text-muted small mb-0">
        <?= esc($operateur['nom_operateur']) ?>
        <span class="badge" style="background:#ecfdf5;color:#065f46"><?= esc($operateur['prefixe_operateur']) ?></span>
      </p>
    </div>
    <div class="alert mb-0 py-2 px-3" style="background:#fef3c7;border:2px solid #fbbf24">
      <strong style="color:#78350f">Commission active : <?= number_format($operateur['commission_transfert_externe'] ?? 0, 2) ?>%</strong>
      <a href="<?= base_url('operateur/editer/' . $operateur['id']) ?>" class="ms-2 btn btn-sm btn-warning">
        <i class="bi bi-pencil"></i> Modifier
      </a>
    </div>
  </div>

  <!-- Filtre dates -->
  <div class="card mb-4" style="border:none;box-shadow:var(--shadow-sm)">
    <div class="card-body py-3">
      <form method="get" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Date debut</label>
          <input type="date" name="date_debut" class="form-control" value="<?= esc($dateDebut ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Date fin</label>
          <input type="date" name="date_fin" class="form-control" value="<?= esc($dateFin ?? '') ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn btn-operator flex-fill">
            <i class="bi bi-funnel me-1"></i>Filtrer
          </button>
          <?php if ($dateDebut || $dateFin): ?>
            <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- KPIs -->
  <?php 
    $gainsTotal = ($stats['gains_retrait'] ?? 0) + ($stats['gains_transfert'] ?? 0) + ($stats['commissions_recues'] ?? 0);
  ?>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
      <div class="stat-card" style="background:#f0f9ff;border:1px solid #bae6fd">
        <div class="stat-value" style="color:#0369a1"><?= number_format($stats['total_transactions'] ?? 0) ?></div>
        <div class="stat-label">Transactions</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card" style="background:#ecfdf5;border:1px solid #a7f3d0">
        <div class="stat-value" style="font-size:1.2rem;color:#047857"><?= number_format($stats['volume_total'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Volume (Ar)</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card" style="background:#fefce8;border:1px solid #fde047">
        <div class="stat-value" style="font-size:1.2rem;color:#a16207"><?= number_format($stats['gains_retrait'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Gains Retrait</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card" style="background:#ecfdf5;border:1px solid #6ee7b7">
        <div class="stat-value" style="font-size:1.2rem;color:#065f46"><?= number_format($stats['gains_transfert'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Gains Transfert</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card" style="background:#eff6ff;border:1px solid #93c5fd">
        <div class="stat-value" style="font-size:1.2rem;color:#1e40af"><?= number_format($stats['commissions_recues'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Commissions</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card" style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;border:none">
        <div class="stat-value" style="color:#fff;font-size:1.5rem"><?= number_format($gainsTotal, 0, ',', ' ') ?></div>
        <div class="stat-label" style="color:rgba(255,255,255,.8)">Total Gains</div>
      </div>
    </div>
  </div>

  <!-- Situation gains -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card h-100" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-header fw-700" style="background:#0891b2;color:#fff">📊 Situation des gains</div>
        <div class="card-body">
          <!-- Section Retrait -->
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold" style="color:#a16207"><i class="bi bi-cash-coin me-2"></i>Gains Retrait</span>
              <span class="fw-bold fs-5" style="color:#a16207"><?= number_format($stats['gains_retrait'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
            <div class="text-end">
              <small class="badge" style="background:#fef3c7;color:#92400e"><?= $gainsTotal > 0 ? number_format(($stats['gains_retrait'] ?? 0) / $gainsTotal * 100, 1) : 0 ?>%</small>
            </div>
          </div>
          
          <!-- Section Transfert Même Opérateur -->
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold" style="color:#065f46"><i class="bi bi-arrow-left-right me-2"></i>Transfert (Même opérateur)</span>
              <span class="fw-bold fs-5" style="color:#065f46"><?= number_format($stats['gains_transfert_meme_op'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
            <div class="text-end">
              <small class="badge" style="background:#d1fae5;color:#065f46"><?= $gainsTotal > 0 ? number_format(($stats['gains_transfert_meme_op'] ?? 0) / $gainsTotal * 100, 1) : 0 ?>%</small>
            </div>
          </div>
          
          <!-- Section Transfert Autres Opérateurs -->
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold" style="color:#047857"><i class="bi bi-send-check me-2"></i>Transfert (Autres opérateurs)</span>
              <span class="fw-bold fs-5" style="color:#047857"><?= number_format($stats['gains_transfert_autre_op'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
            <div class="text-end">
              <small class="badge" style="background:#a7f3d0;color:#065f46"><?= $gainsTotal > 0 ? number_format(($stats['gains_transfert_autre_op'] ?? 0) / $gainsTotal * 100, 1) : 0 ?>%</small>
            </div>
          </div>
          
          <!-- Section Commissions Reçues -->
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold" style="color:#1e40af"><i class="bi bi-cash-stack me-2"></i>Commissions reçues</span>
              <span class="fw-bold fs-5" style="color:#1e40af"><?= number_format($stats['commissions_recues'] ?? 0, 0, ',', ' ') ?> Ar</span>
            </div>
            <div class="text-end">
              <small class="badge" style="background:#dbeafe;color:#1e3a8a"><?= $gainsTotal > 0 ? number_format(($stats['commissions_recues'] ?? 0) / $gainsTotal * 100, 1) : 0 ?>%</small>
            </div>
          </div>
          
          <!-- Total -->
          <div class="d-flex justify-content-between mt-3 pt-3 px-3 py-2 rounded" style="background:linear-gradient(135deg,#ecfeff,#cffafe)">
            <span class="fw-bold fs-5" style="color:#0e7490"><i class="bi bi-calculator me-2"></i>Total des gains</span>
            <span class="fw-bold fs-4" style="color:#0e7490"><?= number_format($gainsTotal, 0, ',', ' ') ?> Ar</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-header fw-700" style="background:#fbbf24;color:#78350f">📤 Montants à envoyer</div>
        <div class="card-body">
          <?php if (empty($stats['montants_a_envoyer'])): ?>
            <div class="empty-state py-4"><i class="bi bi-check-circle text-success fs-2"></i><p class="mt-2 mb-0">Aucun montant à envoyer.</p></div>
          <?php else: ?>
            <?php 
              $totalAEnvoyer = array_sum($stats['montants_a_envoyer']);
            ?>
            <div class="list-group list-group-flush">
              <?php foreach ($stats['montants_a_envoyer'] as $opName => $montant): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                  <div>
                    <span class="fw-semibold fs-6"><i class="bi bi-building me-2 text-danger"></i><?= esc($opName) ?></span>
                    <div class="small text-muted mt-1">
                      <i class="bi bi-percent"></i> <?= $totalAEnvoyer > 0 ? number_format($montant / $totalAEnvoyer * 100, 1) : 0 ?>% du total
                    </div>
                  </div>
                  <span class="badge bg-danger rounded-pill fs-6 px-3 py-2"><?= number_format($montant, 0, ',', ' ') ?> Ar</span>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between mt-3 pt-3 bg-danger bg-opacity-10 px-3 py-2 rounded border border-danger">
              <span class="fw-bold text-danger"><i class="bi bi-cash-stack me-2"></i>Total à envoyer</span>
              <span class="fw-bold text-danger fs-5"><?= number_format($totalAEnvoyer, 0, ',', ' ') ?> Ar</span>
            </div>
            <div class="alert alert-info mt-3 small mb-0">
              <i class="bi bi-info-circle me-1"></i> Ce tableau indique les montants que votre opérateur doit transférer aux autres opérateurs.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Transactions récentes -->
  <div class="card" style="border:none;box-shadow:var(--shadow-md)">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span class="fw-700">Transactions recentes</span>
      <span class="badge badge-operator"><?= count($transactions) ?></span>
    </div>
    <?php if (empty($transactions)): ?>
      <div class="empty-state"><i class="bi bi-inbox"></i><p>Aucune transaction sur cette periode.</p></div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Reference</th>
              <th>Type</th>
              <th>Client</th>
              <th>Montant</th>
              <th>Frais</th>
              <th class="pe-4">Date</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $badgeMap = ['depot'=>'badge-depot','retrait'=>'badge-retrait','transfert'=>'badge-transfert'];
          foreach ($transactions as $tx):
          ?>
            <tr>
              <td class="ps-4"><code class="small"><?= esc(substr($tx['reference_transaction'],0,12)) ?>…</code></td>
              <td><span class="badge <?= $badgeMap[$tx['nom_operation']] ?? 'badge-secondary' ?>"><?= esc($tx['nom_operation']) ?></span></td>
              <td class="small fw-600"><?= esc($tx['telephone_client'] ?? '—') ?></td>
              <td class="fw-700"><?= number_format((float)$tx['montant'],0,',', ' ') ?> <span class="text-muted fw-400 small">Ar</span></td>
              <td class="<?= $tx['frais'] > 0 ? 'text-success fw-600' : 'text-muted' ?>"><?= number_format((float)$tx['frais'],0,',',' ') ?> Ar</td>
              <td class="pe-4 small text-muted"><?= date('d/m/Y H:i',strtotime($tx['date_creation'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

