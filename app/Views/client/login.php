<?= view('layouts/header', ['title' => 'Connexion Client — MobiMoney']) ?>

style="min-height:calc(100vh - 130px);background:linear-gradient(135deg,#E6FAF9 0%,#CCFBF1 50%,#F0FDF9 100%);display:flex;align-items:center"
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
              <div class="form-text mt-2">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                Connexion instantanee — aucun mot de passe requis.
                Si vous n'avez pas de compte, il sera cree automatiquement.
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

          <!-- Prefixes indicatifs -->
          <div class="mt-4 p-3 rounded-3" style="background:var(--bg)">
            <p class="text-muted mb-2" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em">
              Prefixes valides
            </p>
            <div class="d-flex gap-2 flex-wrap">
              <?php foreach ([['034','Telma','#0D9B8C'],['033','Airtel','#0891B2'],['032','Orange','#F59E0B']] as [$p,$n,$c]): ?>
                <span class="badge rounded-pill px-3 py-2" style="background:<?= $c ?>22;color:<?= $c ?>;font-size:.78rem">
                  <strong><?= $p ?></strong> &mdash; <?= $n ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>

<?= view('layouts/footer') ?>
