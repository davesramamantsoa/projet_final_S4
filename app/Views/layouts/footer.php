</main>

<footer>
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 mb-3 mb-md-0">
        <div class="footer-brand mb-1">
          <i class="bi bi-phone-fill text-indigo-400"></i> MobiMoney
        </div>
        <p class="mb-0" style="font-size:.78rem">
          Application de transfert d'argent mobile &mdash; Projet Final S4
        </p>
      </div>
      <div class="col-md-6 text-md-end">
        <span style="font-size:.78rem">
          <i class="bi bi-shield-check me-1 text-success"></i>Transactions securisees
          &nbsp;&bull;&nbsp;
          <i class="bi bi-database me-1"></i>SQLite embarque
          &nbsp;&bull;&nbsp;
          <i class="bi bi-code-slash me-1"></i>CodeIgniter 4
        </span>
      </div>
    </div>
    <hr>
    <p class="text-center mb-0" style="font-size:.75rem">
      &copy; <?= date('Y') ?> MobiMoney &mdash; Tous droits reserves
    </p>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<script>
  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert.fade.show').forEach(el => {
    setTimeout(() => {
      const a = bootstrap.Alert.getOrCreateInstance(el);
      if (a) a.close();
    }, 5000);
  });
</script>
</body>
</html>
