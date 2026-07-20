<?= view('layouts/header', ['title' => 'Historique — MobiMoney']) ?>

<div class="container py-4">

  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= base_url('client/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <h2 class="fw-800 mb-0" style="font-size:1.5rem"><i class="bi bi-clock-history me-2" style="color:var(--client)"></i>Historique</h2>
      <p class="text-muted small mb-0"><?= $total ?> transaction<?= $total > 1 ? 's' : '' ?> au total</p>
    </div>
  </div>

  <div class="card" style="border:none;box-shadow:var(--shadow-md)">
    <?php if (empty($transactions)): ?>
      <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Aucune transaction.</p>
        a href="<?= base_url('client/depot') ?>" class="btn btn-operator btn-sm">Effectuer un depot</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Reference</th>
              <th>Type</th>
              <th>Montant</th>
              <th>Frais</th>
              <th>Destinataire</th>
              <th>Operateur</th>
              <th class="pe-4">Date</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($transactions as $tx):
            $cfg = match($tx['nom_operation']) {
              'depot'     => ['badge-depot',     'bi-arrow-down-circle-fill', 'Depot'],
              'retrait'   => ['badge-retrait',   'bi-arrow-up-circle-fill',   'Retrait'],
              'transfert' => ['badge-transfert', 'bi-send-fill',              'Transfert'],
              default     => ['badge-secondary', 'bi-circle',                 $tx['nom_operation']],
            };
          ?>
            <tr>
              <td class="ps-4">
                <code class="small" style="color:var(--text-muted);font-size:.75rem">
                  <?= esc(substr($tx['reference_transaction'], 0, 15)) ?>…
                </code>
              </td>
              <td><span class="badge <?= $cfg[0] ?>"><i class="bi <?= $cfg[1] ?>"></i> <?= $cfg[2] ?></span></td>
              <td class="fw-700"><?= number_format((float)$tx['montant'], 0, ',', ' ') ?> <span class="text-muted fw-400 small">Ar</span></td>
              <td class="<?= $tx['frais'] > 0 ? 'text-danger fw-600' : 'text-muted' ?>"><?= number_format((float)$tx['frais'], 0, ',', ' ') ?> Ar</td>
              <td class="small text-muted"><?= esc($tx['telephone_destinataire'] ?? '—') ?></td>
              <td><span class="badge badge-operator"><i class="bi bi-building"></i> <?= esc($tx['nom_operateur'] ?? '—') ?></span></td>
              <td class="pe-4 small text-muted"><?= date('d/m/Y H:i', strtotime($tx['date_creation'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-4">
          <nav><ul class="pagination mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
          </ul></nav>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</div>


