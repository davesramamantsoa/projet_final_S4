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
                            <label class="form-label fw-semibold">Numéro(s) destinataire(s)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" name="telephone_destinataire" class="form-control"
                                       placeholder="034XXXXXXX ou 034XXX1, 034XXX2" required>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Envoi unique :</strong> entrez un numéro.<br>
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Envoi multiple (même opérateur uniquement) :</strong> séparez les numéros par une virgule. Le montant sera divisé équitablement.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant Total (Ar)</label>
                            <input type="number" name="montant" class="form-control form-control-lg"
                                   placeholder="Ex: 5000" min="100" step="1" required>
                            <div class="form-text">Frais appliqués selon le montant transféré à chacun.</div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="inclure_frais_retrait" name="inclure_frais_retrait" value="1">
                                <label class="form-check-label fw-semibold" for="inclure_frais_retrait">
                                    <i class="bi bi-cash-coin text-warning me-1"></i>Inclure les frais de retrait pour le(s) destinataire(s)
                                </label>
                            </div>
                            <div class="alert alert-info mt-2 mb-0 py-2 px-3 small">
                                <i class="bi bi-lightbulb me-1"></i> <strong>Note :</strong> Cette option ne s'applique que pour les transferts vers d'autres opérateurs. 
                                Si cochée, vous prenez en charge les frais de retrait du destinataire.
                            </div>
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

