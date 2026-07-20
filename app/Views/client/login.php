<?= view('layouts/header', ['title' => 'Connexion Client — MobiMoney']) ?>

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">

        <div class="text-center mb-4">
          <a href="<?= base_url('/') ?>" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Accueil
          </a>
        </div>

        <div class="login-card p-5">

          <div class="text-center mb-4">
            <div class="login-icon-wrap login-icon-client">
              <i class="bi bi-phone-fill"></i>
            </div>
            <h2 class="fw-800 mb-1" style="font-size:1.6rem">Connexion Client</h2>
            <p class="text-muted small mb-0">Entrez votre numero pour continuer</p>
          </div>

          <form action="<?= base_url('client/login') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>

            <div class="mb-4">
              <label class="form-label">Numero de telephone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                <input type="tel" name="numero_telephone" class="form-control form-control-lg"
                       placeholder="034XXXXXXX" required autofocus
                       style="font-size:1.1rem;letter-spacing:.05em;font-weight:600">
              </div>
             
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
              <i class="bi bi-box-arrow-in-right me-2"></i>Connexion / Inscription
            </button>

            <div class="text-center">
              <span class="text-muted small">Operateur ? </span>
              <a href="<?= base_url('operateur') ?>" class="small fw-600" style="color:var(--operator)">
                Espace administration
              </a>
            </div>
          </form>
          
          <!-- Préfixes acceptés (MON opérateur uniquement) -->
          <?php if (isset($monOperateur) && $monOperateur): ?>
          <div class="mt-4 p-3 rounded-3" style="background:#ecfdf5;border:2px solid #06b6d4">
            <p class="mb-2" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0e7490">
              <i class="bi bi-info-circle me-1"></i>Préfixes acceptés
            </p>
            <div class="mb-2">
              <strong style="font-size:1.1rem;color:#0891b2"><?= esc($monOperateur['nom_operateur']) ?></strong>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <?php 
                $prefixes = array_map('trim', explode(',', $monOperateur['prefixe_operateur']));
                foreach ($prefixes as $p): 
              ?>
                <span class="badge rounded-pill px-3 py-2" style="background:#06b6d4;color:#fff;font-size:.85rem">
                  <strong><?= esc($p) ?></strong>
                </span>
              <?php endforeach; ?>
            </div>
            <p class="text-muted small mt-2 mb-0">
              Exemple : <?= esc($prefixes[0] ?? '034') ?>0001234
            </p>
          </div>
          <?php endif; ?>

        </div>

      </div>
    </div>
  </div>
</div>

