<?= view('layouts/header', ['title' => 'Creer un operateur']) ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold text-operator">Nouvel Operateur</h3>
            </div>

            <div class="card border-0">
                <div class="card-body p-4">
                    <form action="<?= base_url('operateur/creer') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de l'operateur</label>
                            <input type="text" name="nom_operateur" class="form-control"
                                   placeholder="Ex: Telma" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Prefixe</label>
                            <input type="text" name="prefixe_operateur" class="form-control"
                                   placeholder="Ex: 034" maxlength="5" required>
                            <div class="form-text">Les numeros commen&ccedil;ant par ce prefixe seront associes a cet operateur.</div>
                        </div>
                        <div class="alert alert-info small">
                            <i class="bi bi-magic me-1"></i>
                            Les 3 types d'operations (depot, retrait, transfert) et les baremes par defaut seront crees automatiquement.
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-operator btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Creer l'operateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layouts/footer') ?>
