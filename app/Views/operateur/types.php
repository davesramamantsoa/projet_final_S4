<?= view('layouts/header', ['title' => 'Types d\'operations']) ?>

<div class="container my-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold text-operator">
            Types d'operations — <?= esc($operateur['nom_operateur']) ?>
            <span class="badge bg-operator ms-2"><?= esc($operateur['prefixe_operateur']) ?></span>
        </h3>
    </div>

    <div class="row g-4">
        <?php
        $typeConfig = [
            'depot'     => ['success', 'bi-arrow-down-circle-fill', 'Depot',     'Credite le solde client. Frais = 0 Ar.'],
            'retrait'   => ['danger',  'bi-arrow-up-circle-fill',   'Retrait',   'Debite le solde + frais. Client recoit le montant net.'],
            'transfert' => ['primary', 'bi-send-fill',              'Transfert', 'Expediteur paye montant + frais. Destinataire recoit le montant.'],
        ];
        foreach ($types as $type):
            $cfg = $typeConfig[$type['nom_operation']] ?? ['secondary', 'bi-circle', $type['nom_operation'], ''];
        ?>
        <div class="col-md-4">
            <div class="card border-0 h-100">
                <div class="card-header bg-<?= $cfg[0] ?> text-white py-3">
                    <h5 class="mb-0"><i class="bi <?= $cfg[1] ?> me-2"></i><?= $cfg[2] ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><?= $cfg[3] ?></p>

                    <?php if (!empty($type['baremes'])): ?>
                        <table class="table table-sm fee-table">
                            <thead><tr><th>Tranche</th><th>Frais</th></tr></thead>
                            <tbody>
                            <?php foreach ($type['baremes'] as $b): ?>
                                <tr>
                                    <td class="small">
                                        <?= number_format($b['montant_min'], 0, ',', ' ') ?>
                                        —
                                        <?= number_format($b['montant_max'], 0, ',', ' ') ?> Ar
                                    </td>
                                    <td class="small fw-semibold">
                                        <span class="badge bg-operator"><?= number_format($b['montant_frais'], 0, ',', ' ') ?> Ar</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted small">Aucun bareme.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <a href="<?= base_url('operateur/baremes/' . $type['id']) ?>"
                       class="btn btn-outline-operator btn-sm w-100">
                        <i class="bi bi-pencil me-1"></i>Modifier les baremes
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?= view('layouts/footer') ?>
