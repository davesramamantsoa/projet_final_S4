<?= view('layouts/header', ['title' => 'Retrait']) ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= base_url('client/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold"><i class="bi bi-arrow-up-circle-fill text-danger me-2"></i>Retrait</h3>
            </div>

            <?php
                $solde = session()->get('user_id')
                    ? (new \App\Models\UtilisateurModel())->getSolde(session()->get('user_id'))
                    : 0;
            ?>
            <div class="alert alert-info d-flex gap-2">
                <i class="bi bi-wallet2 fs-5 mt-1"></i>
                <div>
                    Solde actuel : <strong><?= number_format($solde, 0, ',', ' ') ?> Ar</strong><br>
                    <small class="text-muted">Des frais s'appliquent selon le montant retiré.</small>
                </div>
            </div>

            <div class="card border-0">
                <div class="card-body p-4">
                    <form action="<?= base_url('client/retrait') ?>" method="post">
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
                            <label class="form-label fw-semibold">Montant à retirer (Ar)</label>
                            <input type="number" name="montant" class="form-control form-control-lg"
                                   placeholder="Ex: 10000" min="100" step="1" required>
                            <div class="form-text">Minimum : 100 Ar — Le total débité inclut les frais.</div>
                        </div>

                        <!-- Tableau barèmes indicatif -->
                        <div class="table-responsive mb-4">
                            <table class="table table-sm fee-table">
                                <thead><tr><th>Tranche</th><th>Frais</th></tr></thead>
                                <tbody>
                                    <tr><td>100 — 1 000 Ar</td><td>50 Ar</td></tr>
                                    <tr><td>1 001 — 5 000 Ar</td><td>50 Ar</td></tr>
                                    <tr><td>5 001 — 10 000 Ar</td><td>100 Ar</td></tr>
                                    <tr><td>10 001 — 25 000 Ar</td><td>200 Ar</td></tr>
                                    <tr><td>25 001 — 50 000 Ar</td><td>400 Ar</td></tr>
                                    <tr><td>50 001 — 100 000 Ar</td><td>800 Ar</td></tr>
                                    <tr><td>100 001 — 250 000 Ar</td><td>1 500 Ar</td></tr>
                                    <tr><td>250 001 — 500 000 Ar</td><td>1 500 Ar</td></tr>
                                    <tr><td>500 001 — 1 000 000 Ar</td><td>2 500 Ar</td></tr>
                                    <tr><td>1 000 001 — 2 000 000 Ar</td><td>3 000 Ar</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg"
                                    onclick="return confirm('Confirmer le retrait ?')">
                                <i class="bi bi-check-circle me-2"></i>Confirmer le retrait
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

