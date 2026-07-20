<?= view('layouts/header', ['title' => 'MobiMoney — Accueil']) ?>

<section class="hero">
  <div class="hero-dots"></div>
  <div class="container position-relative">
    <div class="row justify-content-center text-center mb-5">
      <div class="col-lg-7">
        <div class="mb-4">
          <span class="badge rounded-pill px-3 py-2 mb-4 d-inline-flex align-items-center gap-2"
                style="background:rgba(13,155,140,.25);color:#5EEAD4;font-size:.8rem;font-weight:600;border:1px solid rgba(20,184,166,.3)">
            <i class="bi bi-stars"></i> Plateforme Mobile Money &mdash; Projet Final S4
          </span>
        </div>
        <h1 class="display-4 fw-800 text-white mb-3" style="letter-spacing:-.04em;line-height:1.1">
          Transferts d'argent<br>
          <span style="background:linear-gradient(135deg,#5EEAD4,#14B8A6);-webkit-background-clip:text;-webkit-text-fill-color:transparent">
            simples et rapides
          </span>
        </h1>
        <p class="text-white mb-4" style="opacity:.65;font-size:1.1rem;line-height:1.7">
          Deposez, retirez et transférez de l'argent en quelques secondes.<br>
          Connexion automatique avec votre numero de telephone.
        </p>
      </div>
    </div>

    <!-- Deux espaces -->
    <div class="row g-4 justify-content-center mb-5">
      <div class="col-md-5">
        <a href="<?= base_url('client') ?>" class="hero-card text-center">
          <div class="hero-card-icon mx-auto mb-4"
               style="background:linear-gradient(135deg,#4F46E5,#38BDF8)">
            <i class="bi bi-person-fill text-white fs-2"></i>
          </div>
          <h3 class="text-white fw-700 mb-2" style="font-size:1.4rem">Espace Client</h3>
          <p class="mb-4" style="color:rgba(255,255,255,.6);font-size:.9rem">
            Connectez-vous avec votre numero de telephone.<br>
            Pas de mot de passe requis.
          </p>
          <div class="d-flex justify-content-center gap-3 mb-4">
            <?php foreach ([['bi-arrow-down-circle','Depot'],['bi-arrow-up-circle','Retrait'],['bi-send','Transfert'],['bi-clock-history','Historique']] as [$icon,$label]): ?>
              <div class="text-center">
                <div class="mx-auto mb-1" style="width:36px;height:36px;background:rgba(255,255,255,.12);border-radius:.5rem;display:flex;align-items:center;justify-content:center">
                  <i class="bi <?= $icon ?> text-white"></i>
                </div>
                <span style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:600"><?= $label ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <span class="btn btn-primary btn-lg w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrer
          </span>
        </a>
      </div>

      <div class="col-md-5">
        <a href="<?= base_url('operateur') ?>" class="hero-card text-center">
          <div class="hero-card-icon mx-auto mb-4"
               style="background:linear-gradient(135deg,#0A7A6E,#0D9B8C)">
            <i class="bi bi-shield-lock-fill text-white fs-2"></i>
          </div>
          <h3 class="text-white fw-700 mb-2" style="font-size:1.4rem">Espace Operateur</h3>
          <p class="mb-4" style="color:rgba(255,255,255,.6);font-size:.9rem">
            Gerez les prefixes, les baremes de frais<br>
            et consultez les statistiques.
          </p>
          <div class="d-flex justify-content-center gap-3 mb-4">
            <?php foreach ([['bi-sliders','Baremes'],['bi-bar-chart-line','Stats'],['bi-people','Clients'],['bi-building','Operateurs']] as [$icon,$label]): ?>
              <div class="text-center">
                <div class="mx-auto mb-1" style="width:36px;height:36px;background:rgba(255,255,255,.12);border-radius:.5rem;display:flex;align-items:center;justify-content:center">
                  <i class="bi <?= $icon ?> text-white"></i>
                </div>
                <span style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:600"><?= $label ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <span class="btn btn-operator btn-lg w-100">
            <i class="bi bi-shield-check me-2"></i>Administration
          </span>
        </a>
      </div>
    </div>

    <!-- Operateurs disponibles -->
    <div class="text-center">
      <p class="mb-3" style="color:rgba(255,255,255,.4);font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;font-weight:600">
        Operateurs disponibles
      </p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <?php foreach ([['034','Telma','#14B8A6'],['033','Airtel','#0D9B8C'],['032','Orange','#0A7A6E']] as [$pref,$nom,$col]): ?>
          <div class="px-4 py-2 rounded-pill d-flex align-items-center gap-2"
               style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12)">
            <span class="rounded-circle d-flex align-items-center justify-content-center"
                  style="width:28px;height:28px;background:<?= $col ?>33;font-size:.75rem;font-weight:700;color:<?= $col ?>">
              <?= $pref ?>
            </span>
            <span style="color:rgba(255,255,255,.7);font-size:.875rem;font-weight:600"><?= $nom ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>

<?= view('layouts/footer') ?>
