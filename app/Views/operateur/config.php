<?= view('layouts/header', ['title' => 'Configuration']) ?>

<div class="container-fluid px-4 py-4">

  <div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= base_url('operateur/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h3 class="mb-0 fw-bold"><i class="bi bi-gear me-2 text-operator"></i>Configuration des Opérateurs</h3>
  </div>

  <!-- Mon opérateur -->
  <?php 
    $monOp = $operateurs[0] ?? null;
    if (!$monOp) {
      echo '<div class="alert alert-warning">Aucun opérateur trouvé</div>';
      echo view('layouts/footer');
      return;
    }
  ?>

  <div class="row g-4">
    <!-- Configuration de mon opérateur -->
    <div class="col-lg-6">
      <div class="card" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-header" style="background:#0891b2;color:#fff">
          <i class="bi bi-building me-2"></i><?= esc($monOp['nom_operateur']) ?>
        </div>
        <div class="card-body">
          <form action="<?= base_url('operateur/editer/' . $monOp['id']) ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
              <label class="form-label fw-semibold">Nom de l'opérateur</label>
              <input type="text" name="nom_operateur" class="form-control" 
                     value="<?= esc($monOp['nom_operateur']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Préfixes (séparés par virgule)</label>
              <input type="text" name="prefixe_operateur" class="form-control" 
                     value="<?= esc($monOp['prefixe_operateur']) ?>" 
                     placeholder="Ex: 034, 038" required>
              <div class="form-text">
                <i class="bi bi-info-circle me-1"></i>Ces préfixes identifient les numéros de téléphone de votre opérateur
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">
                <i class="bi bi-percent me-1"></i>Commission sur transferts entrants (%)
              </label>
              <input type="number" name="commission_transfert_externe" class="form-control" 
                     value="<?= esc($monOp['commission_transfert_externe']) ?>" 
                     step="0.1" min="0" placeholder="Ex: 2.0" required>
              <div class="form-text">
                <i class="bi bi-info-circle me-1"></i>Commission prélevée quand d'autres opérateurs envoient vers vous
              </div>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-operator">
                <i class="bi bi-check-circle me-2"></i>Enregistrer mon opérateur
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Configuration des autres opérateurs -->
    <div class="col-lg-6">
      <div class="card" style="border:none;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div class="card-header" style="background:#fbbf24;color:#78350f">
          <i class="bi bi-building me-2"></i>Autres Opérateurs
        </div>
        <div class="card-body">
          <?php if (count($operateurs) > 1): ?>
            <?php foreach (array_slice($operateurs, 1) as $autreOp): ?>
              <div class="card mb-3" style="background:#fefce8;border:1px solid #fde047">
                <div class="card-body">
                  <form action="<?= base_url('operateur/editer/' . $autreOp['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <h6 class="fw-bold mb-3"><?= esc($autreOp['nom_operateur']) ?></h6>
                    
                    <div class="mb-3">
                      <label class="form-label small fw-semibold">Préfixes</label>
                      <input type="text" name="prefixe_operateur" class="form-control form-control-sm" 
                             value="<?= esc($autreOp['prefixe_operateur']) ?>" 
                             placeholder="Ex: 032, 031" required>
                    </div>

                    <div class="mb-3">
                      <label class="form-label small fw-semibold">
                        <i class="bi bi-percent me-1"></i>Commission sur transferts (%)
                      </label>
                      <input type="number" name="commission_transfert_externe" 
                             class="form-control form-control-sm" 
                             value="<?= esc($autreOp['commission_transfert_externe']) ?>" 
                             step="0.1" min="0" required>
                      <div class="form-text">
                        Commission que cet opérateur prélève sur les transferts entrants
                      </div>
                    </div>

                    <input type="hidden" name="nom_operateur" value="<?= esc($autreOp['nom_operateur']) ?>">
                    
                    <div class="d-grid">
                      <button type="submit" class="btn btn-sm" style="background:#fbbf24;color:#78350f">
                        <i class="bi bi-check-circle me-2"></i>Enregistrer
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="alert alert-warning mb-0">
              <i class="bi bi-info-circle me-2"></i>
              Aucun autre opérateur configuré. 
              <a href="<?= base_url('operateur/creer') ?>" class="alert-link">Ajouter un opérateur</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Bouton ajouter un autre opérateur -->
      <div class="d-grid mt-3">
        <a href="<?= base_url('operateur/creer') ?>" class="btn btn-outline-secondary">
          <i class="bi bi-plus-circle me-2"></i>Ajouter un autre opérateur
        </a>
      </div>
    </div>
  </div>

</div>

<?= view('layouts/footer') ?>
