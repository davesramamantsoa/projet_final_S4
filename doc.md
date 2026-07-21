# Fonctionnalités Bonus — MobiMoney

> Petit aléa : fonctionnalités supplémentaires ajoutées au projet Mobile Money.

---

## 1. 🔍 Filtre (Statistiques par période)

L'espace opérateur permet de **filtrer les statistiques par date** pour analyser les gains sur une période spécifique.

### Code côté contrôleur

```php
// app/Controllers/Operateur.php
public function statistiques(int $operateurId)
{
    $dateDebut = $this->request->getGet('date_debut');
    $dateFin   = $this->request->getGet('date_fin');

    return view('operateur/statistiques', [
        'operateur'    => $this->operateurModel->find($operateurId),
        'stats'        => $this->transactionModel->getStatsOperateur(
            $operateurId, $dateDebut, $dateFin
        ),
        'transactions' => $this->transactionModel->getTransactionsOperateur($operateurId),
        'dateDebut'    => $dateDebut,
        'dateFin'      => $dateFin,
    ]);
}
```

### Code côté modèle (filtre SQL)

```php
// app/Models/TransactionModel.php
public function getStatsOperateur(int $operateurId, ?string $dateDebut = null, ?string $dateFin = null): array
{
    $builder = $this->db->table('transactions t')
        ->select('t.*, type_op.nom_operation')
        ->join('types_operations type_op', 't.type_operation_id = type_op.id')
        ->where('type_op.operateur_id', $operateurId);

    // Application du filtre dates
    if ($dateDebut) $builder->where('t.date_creation >=', $dateDebut . ' 00:00:00');
    if ($dateFin)   $builder->where('t.date_creation <=', $dateFin . ' 23:59:59');

    $rows = $builder->get()->getResultArray();
    // ... calcul des stats ...
}
```

### Vue du filtre

```html
<!-- app/Views/operateur/statistiques.php -->
<form method="GET" class="row g-3 align-items-end bg-white p-3 rounded shadow-sm mb-4">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Date début</label>
        <input type="date" name="date_debut" class="form-control"
               value="<?= old('date_debut', $dateDebut ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Date fin</label>
        <input type="date" name="date_fin" class="form-control"
               value="<?= old('date_fin', $dateFin ?? '') ?>">
    </div>
    <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="bi bi-funnel me-1"></i>Filtrer
        </button>
        <a href="<?= base_url('operateur/statistiques/' . $operateur['id']) ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i>
        </a>
    </div>
</form>
```

---

## 2. 🖼️ Upload Image (Photo de profil client)

Ajout d'un upload d'image de profil pour les clients.

### Migration de la table

```sql
ALTER TABLE utilisateurs ADD COLUMN photo_profil TEXT DEFAULT NULL;
```

### Traitement upload (Contrôleur)

```php
// Dans Client.php — méthode pour uploader une photo
public function uploadPhoto()
{
    $file = $this->request->getFile('photo');
    $userId = session()->get('user_id');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'Fichier invalide.');
    }

    // Validation : image uniquement, max 2MB
    if (!$file->hasMimeType(['image/jpeg', 'image/png', 'image/gif'])) {
        return redirect()->back()->with('error', 'Format accepté : JPG, PNG, GIF.');
    }
    if ($file->getSize() > 2 * 1024 * 1024) {
        return redirect()->back()->with('error', 'Taille max : 2 Mo.');
    }

    // Déplacer vers le dossier uploads
    $newName = $file->getRandomName();
    $file->move(FCPATH . 'uploads/profils', $newName);

    // Sauvegarder en base
    $this->utilisateurModel->update($userId, [
        'photo_profil' => 'uploads/profils/' . $newName
    ]);

    return redirect()->back()->with('success', 'Photo mise à jour !');
}
```

### Affichage dans la vue

```html
<!-- app/Views/client/dashboard.php -->
<div class="text-center mb-4">
    <?php if (!empty($utilisateur['photo_profil'])): ?>
        <img src="<?= base_url($utilisateur['photo_profil']) ?>"
             class="rounded-circle border border-3 border-success shadow"
             width="120" height="120" style="object-fit:cover"
             alt="Photo de profil">
    <?php else: ?>
        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
             style="width:120px;height:120px">
            <i class="bi bi-person-fill text-white fs-1"></i>
        </div>
    <?php endif; ?>
</div>

<form action="<?= base_url('client/uploadPhoto') ?>" method="POST"
      enctype="multipart/form-data" class="mb-4">
    <div class="input-group">
        <input type="file" name="photo" class="form-control"
               accept="image/png,image/jpeg,image/gif" required>
        <button type="submit" class="btn btn-outline-success">
            <i class="bi bi-upload me-1"></i>Upload
        </button>
    </div>
</form>
```

---

## 3. 📥 Import CSV (Transactions)

Import en masse des transactions depuis un fichier CSV.

### Formulaire d'import

```html
<!-- app/Views/operateur/import.php -->
<form action="<?= base_url('operateur/importerTransactions') ?>"
      method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Fichier CSV des transactions</label>
        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
    </div>
    <p class="text-muted small">
        Format attendu : <code>telephone;montant;type;date</code><br>
        Exemple : <code>0341234567;5000;depot;2024-01-15</code>
    </p>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-file-earmark-arrow-up me-1"></i>Importer
    </button>
</form>
```

### Traitement PHP

```php
// Dans Operateur.php
public function importerTransactions()
{
    $file = $this->request->getFile('csv_file');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'Fichier invalide.');
    }

    // Lire le fichier CSV
    $handle = fopen($file->getTempName(), 'r');
    $compteur = 0;
    $erreurs = [];

    while (($ligne = fgetcsv($handle, 0, ';')) !== false) {
        [$telephone, $montant, $type, $date] = array_map('trim', $ligne);

        // Ignorer l'en-tête
        if ($telephone === 'telephone') continue;

        // Valider et créer l'utilisateur
        $utilisateur = $this->utilisateurModel->creerOuGetUtilisateur($telephone);
        if (!$utilisateur) {
            $erreurs[] = "Ligne $compteur : téléphone invalide";
            continue;
        }

        // Détecter l'opérateur
        $operateur = $this->operateurModel->detecterParTelephone($telephone);
        if (!$operateur) {
            $erreurs[] = "Ligne $compteur : opérateur non trouvé";
            continue;
        }

        // Récupérer le type d'opération
        $typeOp = $this->typeOperationModel->getByOperateurEtType($operateur['id'], $type);
        if (!$typeOp) {
            $erreurs[] = "Ligne $compteur : type '$type' invalide";
            continue;
        }

        // Calculer les frais
        $frais = $this->baremeFraisModel->calculerFrais($typeOp['id'], (float) $montant);

        // Créer la transaction
        $this->transactionModel->creerTransaction(
            $utilisateur['id'], $typeOp['id'], (float) $montant, $frais
        );

        $compteur++;
    }
    fclose($handle);

    $message = "$compteur transactions importées avec succès.";
    if (!empty($erreurs)) {
        $message .= " Erreurs : " . implode(', ', $erreurs);
    }

    return redirect()->to(base_url('operateur/dashboard'))
        ->with('success', $message);
}
```

---

## 4. 📄 Export PDF (Relevé de compte)

Génération d'un relevé de compte au format PDF pour le client.

### Installation de la librairie DomPDF

```bash
composer require dompdf/dompdf
```

### Contrôleur d'export PDF

```php
// Dans Client.php
public function exportPDF()
{
    $userId = session()->get('user_id');
    $utilisateur = $this->utilisateurModel->find($userId);
    $transactions = $this->transactionModel->getTransactionsUtilisateur($userId, 50);

    // Construire le HTML du PDF
    $html = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            h1 { color: #0D9B8C; font-size: 18px; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #0D9B8C; color: white; padding: 8px; text-align: left; }
            td { padding: 6px; border-bottom: 1px solid #ddd; }
            .solde { text-align: right; font-size: 14px; margin-top: 20px; }
            .footer { text-align: center; color: #888; margin-top: 30px; font-size: 10px; }
        </style>
    </head>
    <body>
        <h1>Relevé de Compte MobiMoney</h1>
        <p><strong>Client :</strong> ' . $utilisateur['numero_telephone'] . '</p>
        <p><strong>Date :</strong> ' . date('d/m/Y H:i') . '</p>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Frais</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($transactions as $tx) {
        $html .= '
                <tr>
                    <td>' . $tx['reference_transaction'] . '</td>
                    <td>' . ucfirst($tx['nom_operation']) . '</td>
                    <td>' . number_format($tx['montant'], 0, ',', ' ') . ' Ar</td>
                    <td>' . number_format($tx['frais'], 0, ',', ' ') . ' Ar</td>
                    <td>' . date('d/m/Y H:i', strtotime($tx['date_creation'])) . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>
        <div class="solde">
            <strong>Solde actuel : ' . number_format($utilisateur['solde'], 0, ',', ' ') . ' Ar</strong>
        </div>
        <div class="footer">
            Document généré automatiquement — MobiMoney &copy; ' . date('Y') . '
        </div>
    </body>
    </html>';

    // Générer le PDF avec DomPDF
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Télécharger le PDF
    $dompdf->stream('releve_compte_' . $utilisateur['numero_telephone'] . '.pdf', [
        'Attachment' => true
    ]);
}
```

### Bouton d'export dans la vue

```html
<!-- app/Views/client/historique.php -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Mon Historique</h5>
    <a href="<?= base_url('client/exportPDF') ?>" class="btn btn-danger">
        <i class="bi bi-filetype-pdf me-1"></i>Télécharger PDF
    </a>
</div>
```

### Route pour l'export

```php
// app/Config/Routes.php — dans le groupe clientAuth
$routes->get('exportPDF', 'Client::exportPDF');
```

---

## Résumé des bonus

| Fonctionnalité | Fichiers modifiés | Technologie |
|----------------|-------------------|-------------|
| 🔍 **Filtre dates** | `Operateur.php`, `TransactionModel.php`, `statistiques.php` | PHP + SQL WHERE |
| 🖼️ **Upload image** | `Client.php`, `dashboard.php`, migration SQL | PHP + HTML form + file move |
| 📥 **Import CSV** | `Operateur.php`, `import.php` | PHP `fgetcsv()` |
| 📄 **Export PDF** | `Client.php`, `historique.php`, `composer require dompdf` | DomPDF + Blade-style HTML |

