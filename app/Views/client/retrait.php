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
            <div class="alert alert-warning d-flex gap-2">
                <i class="bi bi-wallet2 fs-5 mt-1"></i>
                <div>Solde : <strong><?= number_format($solde, 0, ',', ' ') ?> Ar</strong></div>
            </div>

            <div class="card border-0">
                <div class="card-body p-4">
                    <form action="<?= base_url('client/retrait') ?>" method="post" id="retraitForm">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant (À retirer, en Ar)</label>
                            <input type="number" name="montant" class="form-control form-control-lg"
                                   placeholder="Ex: 5000" min="100" step="1" required id="montantInput">
                            <div class="form-text">Frais : <strong id="fraisDisplay">-</strong></div>
                        </div>
                        
                        <script>
                        const baremes = <?= json_encode($baremes ?? []) ?>;
                        document.getElementById('montantInput')?.addEventListener('input', function() {
                            const montant = parseFloat(this.value) || 0;
                            let frais = 0;
                            for (let b of baremes) {
                                if (montant >= b.montant_min && montant <= b.montant_max) {
                                    frais = b.montant_frais;
                                    break;
                                }
                            }
                            document.getElementById('fraisDisplay').textContent = frais.toLocaleString('fr-FR') + ' Ar';
                        });
                        </script>

                        <!-- Tableau barèmes dynamique -->
                        <?php if (!empty($baremes)): ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm fee-table">
                                <thead><tr><th>Tranche</th><th>Frais</th></tr></thead>
                                <tbody>
                                    <?php foreach ($baremes as $b): ?>
                                    <tr>
                                        <td><?= number_format($b['montant_min'], 0, ',', ' ') ?> — <?= number_format($b['montant_max'], 0, ',', ' ') ?> Ar</td>
                                        <td class="fw-semibold"><?= number_format($b['montant_frais'], 0, ',', ' ') ?> Ar</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

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

