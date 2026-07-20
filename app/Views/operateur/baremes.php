<?= view('layouts/header', ['title' => 'Baremes']) ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= base_url('operateur/types/' . $operateur['id']) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold text-operator">
                    Baremes — <?= esc($typeOp['nom_operation']) ?>
                    <small class="text-muted fs-6">(<?= esc($operateur['nom_operateur']) ?>)</small>
                </h3>
            </div>

            <!-- Modifier baremes existants -->
            <div class="card border-0 mb-4">
                <div class="card-header operator-card-header text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Modifier les baremes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($baremes)): ?>
                        <p class="text-muted text-center py-3">Aucun bareme. Ajoutez-en un ci-dessous.</p>
                    <?php else: ?>
                        <form action="<?= base_url('operateur/baremes/' . $typeOp['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="table-responsive">
                                <table class="table fee-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Min (Ar)</th>
                                            <th>Max (Ar)</th>
                                            <th>Frais actuels</th>
                                            <th>Nouveaux frais</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($baremes as $b): ?>
                                        <tr>
                                            <td><?= number_format($b['montant_min'], 0, ',', ' ') ?></td>
                                            <td><?= number_format($b['montant_max'], 0, ',', ' ') ?></td>
                                            <td><span class="badge bg-operator"><?= number_format($b['montant_frais'], 0, ',', ' ') ?> Ar</span></td>
                                            <td style="width:130px">
                                                <input type="number" class="form-control form-control-sm"
                                                       name="baremes[<?= $b['id'] ?>][montant_frais]"
                                                       value="<?= $b['montant_frais'] ?>"
                                                       min="0" step="1" required>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('operateur/supprimerBareme/' . $b['id']) ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Supprimer ce bareme ?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-operator">
                                    <i class="bi bi-floppy me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ajouter un bareme -->
            <div class="card border-0">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Ajouter un bareme</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('operateur/ajouterBareme/' . $typeOp['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Montant Min (Ar)</label>
                                <input type="number" name="montant_min" class="form-control"
                                       placeholder="Ex: 10001" min="0" step="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Montant Max (Ar)</label>
                                <input type="number" name="montant_max" class="form-control"
                                       placeholder="Ex: 25000" min="0" step="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Frais (Ar)</label>
                                <input type="number" name="montant_frais" class="form-control"
                                       placeholder="Ex: 300" min="0" step="1" required>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-lg me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

