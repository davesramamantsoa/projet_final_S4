<?= view('layouts/header', ['title' => 'Éditer un opérateur']) ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold text-operator">Éditer <?= esc($operateur['nom_operateur']) ?></h3>
            </div>

            <div class="card border-0">
                <div class="card-body p-4">
                    <form action="<?= base_url('operateur/editer/' . $operateur['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de l'opérateur</label>
                            <input type="text" name="nom_operateur" class="form-control"
                                   value="<?= esc($operateur['nom_operateur']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Préfixes (séparés par une virgule)</label>
                            <input type="text" name="prefixe_operateur" class="form-control"
                                   value="<?= esc($operateur['prefixe_operateur']) ?>" placeholder="Ex: 034, 038" required>
                            <div class="form-text">Les numéros commençant par ces préfixes seront associés à cet opérateur.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">💰 Commission transfert externe (%)</label>
                            <input type="number" step="0.01" name="commission_transfert_externe" class="form-control form-control-lg"
                                   value="<?= esc($operateur['commission_transfert_externe'] ?? 0) ?>" min="0" max="100" required
                                   style="font-size:1.2rem;font-weight:bold;color:#0891b2">
                            <div class="alert alert-info mt-2 mb-0">
                                <strong>Important :</strong> Cette commission est prise lorsque d'autres opérateurs envoient de l'argent vers vos clients.
                                <br>
                                <strong>Exemple :</strong> Si commission = 2%, un transfert de 1000 Ar génère 20 Ar de commission pour vous.
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-operator btn-lg">
                                <i class="bi bi-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

