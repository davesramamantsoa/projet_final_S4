<?= view('layouts/header', ['title' => 'Dashboard — MobiMoney']) ?>

<div class="container py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h2 class="fw-800 mb-0" style="font-size:1.6rem">Bonjour 👋</h2>
      <p class="text-muted small mb-0"><?= esc($utilisateur['numero_telephone']) ?> &mdash; <?= date('d M Y') ?></p>
    </div>
    <a href="<?= base_url('client/historique') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-clock-history me-1"></i>Voir tout l'historique
    </a>
  </div>

  <div class="row g-4">

    <!-- Solde -->
    <div class="col-12 col-lg-4">
      <div class="balance-card h-100">
        <div class="balance-label"><i class="bi bi-wallet2 me-1"></i>Solde disponible</div>
        <?php $solde = (float)($utilisateur['solde'] ?? 0); ?>
        <div class="balance-amount">
          <?= number_format($solde, 0, ',', ' ') ?>
          <span class="balance-currency">Ar</span>
        </div>
        <div class="balance-phone">
          <i class="bi bi-telephone-fill"></i>
          <?= esc($utilisateur['numero_telephone']) ?>
        </div>
        <div class="mt-3">
          <?php if ($solde > 0): ?>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(16,185,129,.2);color:#6EE7B7;font-size:.75rem">
              <i class="bi bi-check-circle me-1"></i>Compte actif
            </span>
          <?php else: ?>
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.7);font-size:.75rem">
              <i class="bi bi-info-circle me-1"></i>Effectuez un depot pour commencer
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="col-12 col-lg-8">
      <div class="card h-100" style="border:none">
        <div class="card-body p-4">
          <p class="section-title mb-4">Actions rapides</p>
          <div class="row g-3">
            <div class="col-6 col-sm-3">
              <a href="<?= base_url('client/depot') ?>" class="action-btn action-depot w-100">
                <div class="action-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
                <span>Depot</span>
              </a>
            </div>
            <div class="col-6 col-sm-3">
              <a href="<?= base_url('client/retrait') ?>" class="action-btn action-retrait w-100">
                <div class="action-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
                <span>Retrait</span>
              </a>
            </div>
            <div class="col-6 col-sm-3">
              <a href="<?= base_url('client/transfert') ?>" class="action-btn action-transfert w-100">
                <div class="action-icon"><i class="bi bi-send-fill"></i></div>
                <span>Transfert</span>
              </a>
            </div>
            <div class="col-6 col-sm-3">
              <a href="<?= base_url('client/historique') ?>" class="action-btn action-historique w-100">
                <div class="action-icon"><i class="bi bi-clock-history"></i></div>
                <span>Historique</span>
              </a>
            </div>
          </div>

          <!-- Info barèmes -->
          <div class="mt-4 p-3 rounded-3 d-flex gap-3" style="background:#F8FAFC;border:1px solid #E2E8F0">
            <i class="bi bi-info-circle-fill text-primary mt-1 flex-shrink-0"></i>
            <div class="small text-muted">
              <strong class="text-dark">Depot gratuit</strong> &mdash; Aucun frais sur les depots.<br>
              Des frais s'appliquent sur les <strong class="text-dark">retraits</strong> et
              <strong class="text-dark">transferts</strong> selon le montant.
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Dernières transactions -->
  <div class="mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <p class="section-title mb-0">Dernieres transactions</p>
      <?php if (!empty($dernieres)): ?>
        a href="<?= base_url('client/historique') ?>" class="small fw-600" style="color:var(--client-dark)">
          Voir tout <i class="bi bi-arrow-right ms-1"></i>
        </a>
      <?php endif; ?>
    </div>

    <div class="card" style="border:none">
      <?php if (empty($dernieres)): ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <p>Aucune transaction pour le moment.</p>
          <a href="<?= base_url('client/depot') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i>Effectuer un depot
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4">Type</th>
                <th>Montant</th>
                <th>Frais</th>
                <th>Operateur</th>
                <th class="pe-4">Date</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($dernieres as $tx):
              $cfg = match($tx['nom_operation']) {
                'depot'     => ['badge-depot',     'bi-arrow-down-circle-fill', 'Depot'],
                'retrait'   => ['badge-retrait',   'bi-arrow-up-circle-fill',   'Retrait'],
                'transfert' => ['badge-transfert', 'bi-send-fill',              'Transfert'],
                default     => ['badge-secondary', 'bi-circle',                 $tx['nom_operation']],
              };
            ?>
              <tr>
                <td class="ps-4">
                  <span class="badge <?= $cfg[0] ?>">
                    <i class="bi <?= $cfg[1] ?>"></i> <?= $cfg[2] ?>
                  </span>
                </td>
                <td class="fw-700"><?= number_format((float)$tx['montant'], 0, ',', ' ') ?> <span class="text-muted fw-400 small">Ar</span></td>
                <td class="text-muted"><?= number_format((float)$tx['frais'], 0, ',', ' ') ?> Ar</td>
                <td>
                  <span class="badge badge-operator">
                    <i class="bi bi-building"></i> <?= esc($tx['nom_operateur'] ?? '—') ?>
                  </span>
                </td>
                <td class="pe-4 text-muted small">
                  <?= isset($tx['date_creation']) ? date('d/m/Y H:i', strtotime($tx['date_creation'])) : '—' ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?= view('layouts/footer') ?>
