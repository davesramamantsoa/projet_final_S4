<?= view('layouts/header', ['title' => 'Dépôt']) ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= base_url('client/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold"><i class="bi bi-arrow-down-circle-fill text-success me-2"></i>Dépôt</h3>
            </div>

            <div class="alert alert-success d-flex gap-2">
                <i class="bi bi-info-circle-fill mt-1"></i>
                <div><strong>Dépôt gratuit</strong> — Aucun frais appliqué sur les dépôts.</div>
            </div>

            <div class="card border-0">
                <div class="card-body p-4">
                    <form action="<?= base_url('client/depot') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Opérateur</label>
                            <select name="operateur_id" class="form-select" required>
                                <option value="">— Choisir un opérateur —</option>
                                <?php foreach ($operateurs as $op): ?>
                                    <option value="<?= $op['id'] ?>">
                                        <?= esc($op['nom_operateur']) ?> (<?= esc($op['prefixe_operateur']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant (Ar)</label>
                            <input type="number" name="montant" class="form-control form-control-lg"
                                   placeholder="Ex: 5000" min="100" step="1" required>
                            <div class="form-text">Minimum : 100 Ar</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Confirmer le dépôt
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?= view('layouts/footer') ?>
