<?= view('layouts/header', ['title' => 'Statistiques — MobiMoney']) ?>

<div class="container-fluid px-4 py-4">

  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <h2 class="fw-800 mb-0" style="font-size:1.5rem">Statistiques</h2>
      <p class="text-muted small mb-0">
        <?= esc($operateur['nom_operateur']) ?>
        <span class="badge badge-operator ms-1"><?= esc($operateur['prefixe_operateur']) ?></span>
      </p>
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
  <?php $gainsTotal = ($stats['gains_retrait'] ?? 0) + ($stats['gains_transfert'] ?? 0); ?>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
      <div class="stat-card client">
        <div class="stat-value"><?= number_format($stats['total_transactions'] ?? 0) ?></div>
        <div class="stat-label">Transactions</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card info">
        <div class="stat-value" style="font-size:1.2rem"><?= number_format($stats['volume_total'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Volume (Ar)</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card warning">
        <div class="stat-value" style="font-size:1.2rem"><?= number_format($stats['gains_retrait'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Gains Retrait</div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-card info">
        <div class="stat-value" style="font-size:1.2rem"><?= number_format($stats['gains_transfert'] ?? 0, 0, ',', ' ') ?></div>
        <div class="stat-label">Gains Transfert</div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="stat-card operator" style="background:linear-gradient(135deg,var(--operator-dark),var(--operator));color:#fff;border:none">
        <div class="stat-value" style="color:#fff;font-size:1.8rem"><?= number_format($gainsTotal, 0, ',', ' ') ?></div>
        <div class="stat-label" style="color:rgba(255,255,255,.7)">Total Gains (Ar)</div>
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

<?= view('layouts/footer') ?>
