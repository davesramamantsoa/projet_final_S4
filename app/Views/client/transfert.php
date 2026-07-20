<?= view('layouts/header', ['title' => 'Transfert']) ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= base_url('client/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold"><i class="bi bi-send-fill text-primary me-2"></i>Transfert</h3>
            </div>

            <?php
                $solde = session()->get('user_id')
                    ? (new \App\Models\UtilisateurModel())->getSolde(session()->get('user_id'))
                    : 0;
            ?>
            <div class="alert alert-info d-flex gap-2">
                <i class="bi bi-wallet2 fs-5 mt-1"></i>
                <div>Solde : <strong><?= number_format($solde, 0, ',', ' ') ?> Ar</strong></div>
            </div>

            <div class="card border-0">
                <div class="card-body p-4">
                    <form action="<?= base_url('client/transfert') ?>" method="post">
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

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Numéro destinataire</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="tel" name="telephone_destinataire" class="form-control"
                                       placeholder="034XXXXXXX" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant (Ar)</label>
                            <input type="number" name="montant" class="form-control form-control-lg"
                                   placeholder="Ex: 5000" min="100" step="1" required>
                            <div class="form-text">Frais appliqués selon le montant (voir barème retrait).</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"
                                    onclick="return confirm('Confirmer le transfert ?')">
                                <i class="bi bi-send me-2"></i>Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?= view('layouts/footer') ?>
