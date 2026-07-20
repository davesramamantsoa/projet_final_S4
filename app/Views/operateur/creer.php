<?= view('layouts/header', ['title' => 'Créer un Opérateur']) ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow">
                <div class="card-header bg-operator text-white">
                    <h4 class="mb-0"><i class="bi bi-building-add me-2"></i>Nouvel Opérateur</h4>
                </div>
                <div class="card-body p-4">
                    <form action="<?= base_url('operateur/creer') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de l'opérateur</label>
                            <input type="text" name="nom_operateur" class="form-control" placeholder="Ex: Telma" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Préfixes (séparés par virgule)</label>
                            <input type="text" name="prefixe_operateur" class="form-control" placeholder="Ex: 034, 038" required>
                            <small class="text-muted">Les numéros commençant par ces préfixes seront associés à cet opérateur</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Commission externe (%)</label>
                            <input type="number" step="0.01" name="commission_transfert_externe" class="form-control" placeholder="Ex: 2.5" value="0">
                            <small class="text-muted">Pourcentage pris pour les transferts entrants des autres opérateurs</small>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" placeholder="Ex: telma" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-operator btn-lg">
                                <i class="bi bi-save me-2"></i>Créer l'opérateur
                            </button>
                            <a href="<?= base_url('operateur') ?>" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layouts/footer') ?>
