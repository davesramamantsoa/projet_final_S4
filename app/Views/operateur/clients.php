<?= view('layouts/header', ['title' => 'Comptes Clients']) ?>

<div class="container-fluid px-4 my-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold text-operator"><i class="bi bi-people-fill me-2"></i>Comptes Clients</h3>
    </div>

    <?php foreach ($clientsParOperateur as $groupe):
        $op      = $groupe['operateur'];
        $clients = $groupe['clients'];
        $totalSoldes = $groupe['total_soldes'];
    ?>
        <div class="card mb-4 operator-card">
            <div class="card-header operator-card-header text-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>
                        <?= esc($op['nom_operateur']) ?>
                        <span class="badge bg-white text-operator ms-2"><?= esc($op['prefixe_operateur']) ?></span>
                    </h5>
                    <span class="badge bg-white text-operator">
                        <?= count($clients) ?> client<?= count($clients) !== 1 ? 's' : '' ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($clients)): ?>
                    <p class="text-muted text-center py-4">Aucun client avec les préfixes <?= esc($op['prefixe_operateur']) ?> pour l'instant.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Numéro de téléphone</th>
                                    <th>Solde</th>
                                    <th class="pe-4">Date d'inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($clients as $c): ?>
                                <tr>
                                    <td class="ps-4">
                                        <i class="bi bi-person-circle text-muted me-2"></i>
                                        <strong><?= esc($c['numero_telephone']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="fw-semibold <?= $c['solde'] > 0 ? 'text-success' : 'text-muted' ?>">
                                            <?= number_format($c['solde'], 0, ',', ' ') ?> Ar
                                        </span>
                                    </td>
                                    <td class="pe-4 text-muted small">
                                        <?= isset($c['date_creation']) ? date('d/m/Y H:i', strtotime($c['date_creation'])) : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="ps-4">Total <?= count($clients) ?> clients</td>
                                    <td class="text-success"><?= number_format($totalSoldes, 0, ',', ' ') ?> Ar</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= view('layouts/footer') ?>
