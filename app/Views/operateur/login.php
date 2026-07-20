<?= view('layouts/header', ['title' => 'Administration — MobiMoney']) ?>

<div style="min-height:calc(100vh - 130px);background:linear-gradient(135deg,#E6FAF9 0%,#CCFBF1 50%,#F0FDF9 100%);display:flex;align-items:center">
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
            <div class="login-icon-wrap login-icon-operator">
              <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h2 class="fw-800 mb-1" style="font-size:1.6rem">Administration</h2>
            <p class="text-muted small mb-0">Espace operateur MobiMoney</p>
          </div>

          <form action="<?= base_url('operateur/login') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
              <label class="form-label">Identifiant</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                <input type="text" name="username" class="form-control"
                       placeholder="admin" required autofocus value="admin">
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Mot de passe</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required id="pwdInput">
                <button type="button" class="btn btn-outline-secondary border-start-0" id="togglePwd"
                        style="border:1.5px solid var(--border);border-left:none;border-radius:0 var(--radius) var(--radius) 0">
                  <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-operator btn-lg w-100 mb-3">
              <i class="bi bi-shield-check me-2"></i>Connexion
            </button>

            <div class="text-center">
              <span class="text-muted small">Client ? </span>
              <a href="<?= base_url('client') ?>" class="small fw-600" style="color:var(--client)">
                Espace client
              </a>
            </div>
          </form>

          <div class="mt-4 p-3 rounded-3"
               style="background:rgba(13,155,140,.08);border:1px solid rgba(13,155,140,.2)">
            <p class="mb-1" style="font-size:.75rem;font-weight:700;color:var(--operator-dark);text-transform:uppercase;letter-spacing:.08em">
              Compte par defaut
            </p>
            <p class="mb-0 small text-muted">
              Identifiant : <code class="fw-700">admin</code>&nbsp;&nbsp;
              Mot de passe : <code class="fw-700">Admin@1234</code>
            </p>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function () {
  const input = document.getElementById('pwdInput');
  const icon  = document.getElementById('eyeIcon');
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>

<?= view('layouts/footer') ?>
