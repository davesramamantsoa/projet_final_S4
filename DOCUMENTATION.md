# Documentation Technique — Mobile Money (MobiMoney)

> Application de gestion d'argent mobile (Mobile Money) développée avec **CodeIgniter 4**, **SQLite** et **Bootstrap 5**.
>
> **Projet Final S4** — Architecture MVC complète avec espaces Client & Opérateur.

---

## Table des matières

1. [Architecture globale](#1-architecture-globale)
2. [Base de données](#2-base-de-données)
3. [Modèles (Models)](#3-modèles-models)
4. [Contrôleurs (Controllers)](#4-contrôleurs-controllers)
5. [Vues (Views)](#5-vues-views)
6. [Filtres d'authentification](#6-filtres-dauthentification)
7. [Routes](#7-routes)
8. [Logique métier](#8-logique-métier)
9. [Installation & Déploiement](#9-installation--déploiement)

---

## 1. Architecture globale

```
projet_final_S4/
├── app/
│   ├── Config/          # Configuration CI4 (DB, Routes, Filters...)
│   ├── Controllers/     # Logique applicative (Home, Client, Operateur)
│   ├── Database/
│   │   ├── Migrations/  # Schéma de la base SQLite
│   │   └── Seeds/       # Données de démonstration
│   ├── Filters/         # Authentification (ClientAuth, OperateurAuth)
│   ├── Models/          # Accès aux données (6 modèles)
│   └── Views/           # Templates (client/, operateur/, layouts/)
├── public/              # Point d'entrée (index.php) + assets CSS/JS
├── writable/            # Base SQLite, logs, cache, sessions
├── database.sql         # Schéma brut SQLite
├── Taches.md            # Répartition des tâches du binôme
├── DOCUMENTATION.md     # Ce fichier
└── README.md            # Instructions rapides
```

**Stack technique :**

| Couche | Technologie |
|--------|-------------|
| Backend | PHP 8.1+, CodeIgniter 4.5 |
| Base de données | SQLite3 (fichier unique) |
| Frontend | Bootstrap 5.3, Vanilla JS |
| Auth | Sessions PHP (filtres CI4) |

---

## 2. Base de données

### Schéma relationnel (6 tables)

```sql
-- Table principale des utilisateurs/clients
CREATE TABLE utilisateurs (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_telephone  TEXT UNIQUE NOT NULL,   -- Login = numéro
    solde             REAL DEFAULT 0,          -- Solde actuel
    date_creation     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Opérateurs téléphoniques (Telma, Airtel, Orange...)
CREATE TABLE operateurs (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_operateur      TEXT NOT NULL,
    prefixe_operateur  TEXT NOT NULL,          -- "034,038" (multi-prefixes)
    commission_transfert_externe REAL DEFAULT 0, -- % commission entrante
    username           TEXT UNIQUE,            -- Login admin
    password           TEXT                    -- Hash du mot de passe
);

-- Types d'opérations: depot / retrait / transfert
CREATE TABLE types_operations (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id    INTEGER NOT NULL,
    nom_operation   TEXT NOT NULL
        CHECK(nom_operation IN ('depot', 'retrait', 'transfert')),
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id) ON DELETE CASCADE
);

-- Barèmes de frais par tranche de montant
CREATE TABLE baremes_frais (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id   INTEGER NOT NULL,
    montant_min         REAL NOT NULL,
    montant_max         REAL NOT NULL,
    montant_frais       REAL NOT NULL,         -- Frais fixe pour la tranche
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id) ON DELETE CASCADE
);

-- Transactions enregistrées
CREATE TABLE transactions (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    reference_transaction   TEXT UNIQUE NOT NULL,  -- Format: TXN{hex}{timestamp}
    utilisateur_id          INTEGER NOT NULL,
    type_operation_id       INTEGER NOT NULL,
    montant                 REAL NOT NULL,
    frais                   REAL DEFAULT 0,
    telephone_destinataire  TEXT,              -- Pour transferts
    montant_a_envoyer       REAL DEFAULT 0,    -- Montant reçu par destinataire
    commission_externe      REAL DEFAULT 0,    -- Commission inter-opérateur
    date_creation           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id)    REFERENCES utilisateurs(id),
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id)
);

-- Traçabilité des mouvements de solde
CREATE TABLE historique_soldes (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id    INTEGER NOT NULL,
    transaction_id    INTEGER NOT NULL,
    solde_precedent   REAL NOT NULL,
    nouveau_solde     REAL NOT NULL,
    date_creation     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id)  REFERENCES utilisateurs(id),
    FOREIGN KEY (transaction_id)  REFERENCES transactions(id)
);
```

### Relations clés

```
utilisateurs (1) ──────< (N) transactions
operateurs   (1) ──────< (N) types_operations
types_operations (1) ──< (N) baremes_frais
transactions (1) ──────< (N) historique_soldes
transactions (N) ──────> (1) types_operations ──> (1) operateurs
```

> **Note :** Les contraintes de clés étrangères sont activées par CI4. Les index sont créés sur `numero_telephone`, `utilisateur_id`, `type_operation_id` et `date_creation` pour optimiser les performances.

---

## 3. Modèles (Models)

### 3.1 UtilisateurModel — Gestion des clients

```php
<?php
// app/Models/UtilisateurModel.php

class UtilisateurModel extends Model
{
    protected $table         = 'utilisateurs';
    protected $allowedFields = ['numero_telephone', 'solde'];

    /**
     * Auto-inscription : crée un compte si le numéro n'existe pas.
     * Permet la connexion sans mot de passe.
     */
    public function creerOuGetUtilisateur(string $numero): ?array
    {
        $existant = $this->getByTelephone($numero);
        if ($existant) return $existant;

        $this->insert(['numero_telephone' => $numero, 'solde' => 0]);
        return $this->getByTelephone($numero);
    }

    /**
     * Crédite ou débite le solde d'un utilisateur.
     * Retourne false si le solde deviendrait négatif.
     */
    public function mettreAJourSolde(int $id, float $montant, string $type = 'credit'): bool
    {
        $u = $this->find($id);
        $nouveauSolde = ($type === 'credit')
            ? $u['solde'] + $montant
            : $u['solde'] - $montant;

        if ($nouveauSolde < 0) return false;
        return (bool) $this->update($id, ['solde' => $nouveauSolde]);
    }

    /**
     * Récupère les clients appartenant à un opérateur (par préfixe).
     * Support multi-prefixes : "034,038"
     */
    public function getUtilisateursByPrefixe(string $prefixes): array
    {
        $prefixeArray = array_map('trim', explode(',', $prefixes));
        $builder = $this->builder();
        $builder->groupStart();
        foreach ($prefixeArray as $i => $prefixe) {
            $i === 0
                ? $builder->like('numero_telephone', $prefixe, 'after')
                : $builder->orLike('numero_telephone', $prefixe, 'after');
        }
        $builder->groupEnd();
        return $builder->orderBy('date_creation', 'DESC')->get()->getResultArray();
    }
}
```

**Points clés :**
- `creerOuGetUtilisateur()` implémente le principe **find-or-create** — pas de formulaire d'inscription
- `mettreAJourSolde()` gère le **verrouillage applicatif** (pas de solde négatif)
- `getUtilisateursByPrefixe()` utilise un **LIKE** avec `after` pour matcher les numéros commençant par un préfixe

### 3.2 OperateurModel — Détection et gestion des opérateurs

```php
<?php
// app/Models/OperateurModel.php

class OperateurModel extends Model
{
    protected $table         = 'operateurs';

    /**
     * Détecte l'opérateur d'un numéro de téléphone.
     * Parcourt tous les opérateurs et vérifie si le numéro commence
     * par l'un de leurs préfixes (support multi-prefixes avec virgule).
     */
    public function detecterParTelephone(string $numero): ?array
    {
        foreach ($this->findAll() as $op) {
            $prefixes = array_map('trim', explode(',', $op['prefixe_operateur']));
            foreach ($prefixes as $prefixe) {
                if ($prefixe !== '' && strpos($numero, $prefixe) === 0) return $op;
            }
        }
        return null;
    }

    /**
     * Vérifie si un préfixe est déjà utilisé par un autre opérateur.
     * Utile pour éviter les conflits lors de la création.
     */
    public function prefixeExiste(string $prefixeAVerifier, ?int $excludeId = null): bool
    {
        $prefixesToCheck = array_map('trim', explode(',', $prefixeAVerifier));
        foreach ($this->findAll() as $op) {
            if ($excludeId && $op['id'] == $excludeId) continue;
            $prefixesExistants = array_map('trim', explode(',', $op['prefixe_operateur']));
            foreach ($prefixesToCheck as $p) {
                if (in_array($p, $prefixesExistants)) return true;
            }
        }
        return false;
    }
}
```

**Points clés :**
- Détection d'opérateur par **préfixe de numéro** (ex: 034 → Telma)
- Support des **multi-prefixes** (ex: "034,038" pour un même opérateur)
- Validation d'unicité des préfixes à la création

### 3.3 TransactionModel — Cœur des opérations financières

```php
<?php
// app/Models/TransactionModel.php

class TransactionModel extends Model
{
    /**
     * Génère une référence unique formatée : TXN{hexAleatoire}{timestamp}
     * Exemple : TXN3A8F2C1E9B1704067200
     */
    public function genererReference(): string
    {
        return 'TXN' . strtoupper(bin2hex(random_bytes(5))) . time();
    }

    /**
     * Crée une transaction complète dans la base.
     * Paramètres : utilisateur, type_op, montant, frais,
     *              destinataire (optionnel), montant envoyé, commission externe
     */
    public function creerTransaction(
        int    $utilisateurId,
        int    $typeOperationId,
        float  $montant,
        float  $frais,
        ?string $telephoneDestinataire = null,
        float  $montantAEnvoyer = 0,
        float  $commissionExterne = 0
    ): ?array {
        $data = [
            'reference_transaction'  => $this->genererReference(),
            'utilisateur_id'         => $utilisateurId,
            'type_operation_id'      => $typeOperationId,
            'montant'                => $montant,
            'frais'                  => $frais,
            'telephone_destinataire' => $telephoneDestinataire,
            'montant_a_envoyer'      => $montantAEnvoyer,
            'commission_externe'     => $commissionExterne,
        ];
        if ($this->insert($data)) {
            return $this->where('reference_transaction', $data['reference_transaction'])->first();
        }
        return null;
    }
}
```

#### Statistiques opérateur — `getStatsOperateur()`

Cette méthode est le **moteur analytique** du projet. Elle calcule :
- Le nombre total de transactions
- Le volume total d'argent traité
- Les gains par type d'opération (retrait / transfert)

```php
public function getStatsOperateur(int $operateurId, ?string $dateDebut = null, ?string $dateFin = null): array
{
    $builder = $this->db->table('transactions t')
        ->select('t.montant, t.frais, t.telephone_destinataire, t.montant_a_envoyer, t.commission_externe, type_op.nom_operation')
        ->join('types_operations type_op', 't.type_operation_id = type_op.id')
        ->where('type_op.operateur_id', $operateurId);

    // Filtre optionnel par période
    if ($dateDebut) $builder->where('t.date_creation >=', $dateDebut . ' 00:00:00');
    if ($dateFin)   $builder->where('t.date_creation <=', $dateFin . ' 23:59:59');

    $rows = $builder->get()->getResultArray();

    $stats = [
        'total_transactions' => count($rows),
        'volume_total'       => 0,
        'gains_retrait'      => 0,
        'gains_transfert_meme_op' => 0,    // Transferts internes
        'gains_transfert_autre_op' => 0,   // Transferts externes
        'montants_a_envoyer' => [],         // Dettes par opérateur
    ];

    foreach ($rows as $tx) {
        $stats['volume_total'] += $tx['montant'];

        if ($tx['nom_operation'] === 'retrait') {
            $stats['gains_retrait'] += $tx['frais'];
        }

        if ($tx['nom_operation'] === 'transfert') {
            $destOp = !empty($tx['telephone_destinataire'])
                ? (new OperateurModel())->detecterParTelephone($tx['telephone_destinataire'])
                : null;

            if ($destOp && $destOp['id'] != $operateurId) {
                $stats['gains_transfert_autre_op'] += $tx['frais'];
            } else {
                $stats['gains_transfert_meme_op'] += $tx['frais'];
            }
        }
    }

    return $stats;
}
```

### 3.4 BaremeFraisModel — Calcul des frais

```php
<?php
// app/Models/BaremeFraisModel.php

class BaremeFraisModel extends Model
{
    /**
     * Calcule les frais pour un montant donné selon les barèmes configurés.
     * Parcourt les tranches jusqu'à trouver [min <= montant <= max].
     * Retourne 0 si aucune tranche ne correspond.
     */
    public function calculerFrais(int $typeOperationId, float $montant): float
    {
        $b = $this->where('type_operation_id', $typeOperationId)
                  ->where('montant_min <=', $montant)
                  ->where('montant_max >=', $montant)
                  ->first();
        return $b ? (float) $b['montant_frais'] : 0.0;
    }

    /**
     * Crée les barèmes par défaut pour un type d'opération.
     * Les frais dépôt sont toujours 0 (multiplicateur = 0).
     */
    public function creerBaremesParDefaut(int $typeOperationId, string $nomOperation): void
    {
        $mult = ($nomOperation === 'depot') ? 0 : 1;
        $baremes = [
            [100,     1000,    50   * $mult],
            [1001,    5000,    50   * $mult],
            [5001,    10000,   100  * $mult],
            [10001,   25000,   200  * $mult],
            [25001,   50000,   400  * $mult],
            [50001,   100000,  800  * $mult],
            [100001,  250000,  1500 * $mult],
            [250001,  500000,  1500 * $mult],
            [500001,  1000000, 2500 * $mult],
            [1000001, 2000000, 3000 * $mult],
        ];
        foreach ($baremes as [$min, $max, $frais]) {
            $this->insert([
                'type_operation_id' => $typeOperationId,
                'montant_min'       => $min,
                'montant_max'       => $max,
                'montant_frais'     => $frais,
            ]);
        }
    }
}
```

### 3.5 TypeOperationModel et HistoriqueSoldeModel

```php
<?php
// app/Models/TypeOperationModel.php

class TypeOperationModel extends Model
{
    /**
     * Initialisation automatique : crée depot/retrait/transfert
     * avec leurs barèmes par défaut pour un nouvel opérateur.
     */
    public function creerTypesParDefaut(int $operateurId): void
    {
        $baremeFraisModel = new BaremeFraisModel();
        foreach (['depot', 'retrait', 'transfert'] as $nom) {
            $this->insert(['operateur_id' => $operateurId, 'nom_operation' => $nom]);
            $typeId = $this->db->insertID();
            $baremeFraisModel->creerBaremesParDefaut($typeId, $nom);
        }
    }
}

// app/Models/HistoriqueSoldeModel.php
class HistoriqueSoldeModel extends Model
{
    /**
     * Enregistre l'état du solde avant/après chaque transaction.
     * Garantit la traçabilité et l'auditabilité des mouvements.
     */
    public function enregistrer(int $utilisateurId, int $transactionId, float $soldePrecedent, float $nouveauSolde): void
    {
        $this->insert([
            'utilisateur_id'  => $utilisateurId,
            'transaction_id'  => $transactionId,
            'solde_precedent' => $soldePrecedent,
            'nouveau_solde'   => $nouveauSolde,
        ]);
    }
}
```

---

## 4. Contrôleurs (Controllers)

### 4.1 ClientController — Espace client complet

Le contrôleur `Client.php` gère l'intégralité de l'expérience utilisateur final.

#### Authentification sans mot de passe

```php
<?php
// app/Controllers/Client.php

class Client extends BaseController
{
    // ─── Connexion automatique par numéro de téléphone ───

    public function login()
    {
        $numero = trim($this->request->getPost('numero_telephone') ?? '');

        // Nettoyage du numéro et détection de l'opérateur
        $numero = preg_replace('/\s+/', '', $numero);
        $operateur = $this->operateurModel->detecterParTelephone($numero);

        // Vérification : seul "MON opérateur" (le premier) peut se connecter
        $monOperateur = $operateurs[0];
        if (!$operateur || $operateur['id'] != $monOperateur['id']) {
            return redirect()->back()->with('error',
                "Accès refusé. Seuls les numéros {$monOperateur['nom_operateur']} peuvent se connecter."
            );
        }

        // Création auto du compte si inexistant
        $utilisateur = $this->utilisateurModel->creerOuGetUtilisateur($numero);

        // Session utilisateur
        session()->set([
            'user_id'           => $utilisateur['id'],
            'numero_telephone'  => $utilisateur['numero_telephone'],
            'user_type'         => 'client',
            'operateur_id'      => $operateur['id'],
        ]);

        return redirect()->to(base_url('client/dashboard'));
    }
}
```

#### Opération de dépôt

```php
public function depot()
{
    if ($this->request->is('get')) {
        return view('client/depot');
    }

    $montant = (float) $this->request->getPost('montant');
    $userId  = session()->get('user_id');

    // Validation : montant minimum 100 Ar
    if ($montant < 100) {
        return redirect()->back()->with('error', 'Montant minimum : 100 Ar.');
    }

    // Pour un dépôt : frais = 0 (crédit pur)
    $frais = $this->baremeFraisModel->calculerFrais($typeOp['id'], $montant); // = 0

    // 1. Enregistrer le solde avant
    $soldePrecedent = $this->utilisateurModel->getSolde($userId);

    // 2. Créditer le compte
    $this->utilisateurModel->mettreAJourSolde($userId, $montant, 'credit');

    // 3. Créer la transaction
    $transaction = $this->transactionModel->creerTransaction(
        $userId, $typeOp['id'], $montant, $frais
    );

    // 4. Enregistrer le solde après (traçabilité)
    $nouveauSolde = $this->utilisateurModel->getSolde($userId);
    $this->historiqueSoldeModel->enregistrer(
        $userId, $transaction['id'], $soldePrecedent, $nouveauSolde
    );
}
```

#### Opération de retrait avec vérification de solde

```php
public function retrait()
{
    $montant = (float) $this->request->getPost('montant');
    $frais   = $this->baremeFraisModel->calculerFrais($typeOp['id'], $montant);
    $total   = $montant + $frais;

    // Vérification : solde >= montant + frais
    if ($utilisateur['solde'] < $total) {
        return redirect()->back()->with('error', sprintf(
            'Solde insuffisant. Solde : %s Ar — Total requis : %s Ar (frais : %s Ar).',
            number_format($utilisateur['solde'], 0, ',', ' '),
            number_format($total, 0, ',', ' '),
            number_format($frais, 0, ',', ' ')
        ));
    }

    // Débiter le compte (montant + frais)
    $this->utilisateurModel->mettreAJourSolde($userId, $total, 'debit');

    // Enregistrer la transaction
    $transaction = $this->transactionModel->creerTransaction(
        $userId, $typeOp['id'], $montant, $frais
    );
}
```

#### Transfert multi-destinataire avec commissions

```php
public function transfert()
{
    $montantGlobal = (float) $this->request->getPost('montant');
    $destinataireRaw = trim($this->request->getPost('telephone_destinataire') ?? '');

    // Support des envois multiples : "0321111111,0322222222"
    $destinataires = array_filter(explode(',', str_replace(' ', '', $destinataireRaw)));

    // Vérification : tous les destinataires doivent être du même opérateur
    if (count($destinataires) > 1) {
        $premierDestOp = $this->operateurModel->detecterParTelephone($destinataires[0]);
        foreach ($destinataires as $dest) {
            $destOp = $this->operateurModel->detecterParTelephone($dest);
            if (!$destOp || $destOp['id'] != $premierDestOp['id']) {
                return redirect()->back()->with('error',
                    'Pour les envois multiples, tous les destinataires doivent être du même opérateur.'
                );
            }
        }
    }

    $montantParDestinataire = $montantGlobal / count($destinataires);

    foreach ($destinataires as $destinataire) {
        // Si destinataire est d'un autre opérateur → commission
        if ($destOperateur && $destOperateur['id'] != $operateurId) {
            $commissionDest = $montantAEnvoyer * ($destOperateur['commission_transfert_externe'] / 100);
        }

        // Débit expéditeur
        $this->utilisateurModel->mettreAJourSolde($userId, $totalDebite, 'debit');
        // Crédit destinataire
        $this->utilisateurModel->mettreAJourSolde($destinataireUser['id'], $montantRecu, 'credit');
    }
}
```

### 4.2 OperateurController — Administration

```php
<?php
// app/Controllers/Operateur.php

class Operateur extends BaseController
{
    // ─── Authentification via .env (variables d'environnement) ───

    public function login()
    {
        $username = trim($this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';

        // Identifiants stockés dans le fichier .env (pas en base)
        $validUsername = env('operator.username', 'admin');
        $validPassword = env('operator.password', 'admin');

        if ($username === $validUsername && $password === $validPassword) {
            session()->set(['user_type' => 'operator', 'username' => $username]);
            return redirect()->to(base_url('operateur/dashboard'));
        }
    }

    // ─── Dashboard avec métriques globales ───

    public function dashboard()
    {
        $operateurs = $this->operateurModel->findAll();
        $stats = [];
        foreach ($operateurs as $op) {
            $stats[$op['id']] = $this->transactionModel->getStatsOperateur($op['id']);
        }
        return view('operateur/dashboard', compact('operateurs', 'stats'));
    }

    // ─── Gestion des barèmes (CRUD) ───

    public function baremes(int $typeOperationId)
    {
        if ($this->request->is('get')) {
            return view('operateur/baremes', [
                'typeOp'   => $this->typeOperationModel->find($typeOperationId),
                'baremes'  => $this->baremeFraisModel->getBaremesByTypeOperation($typeOperationId),
            ]);
        }

        // POST : Mise à jour en masse des frais
        foreach ($this->request->getPost('baremes') ?? [] as $id => $data) {
            $this->baremeFraisModel->update((int) $id, [
                'montant_frais' => (float) ($data['montant_frais'] ?? 0),
            ]);
        }
    }

    public function ajouterBareme(int $typeOperationId)
    {
        $min   = (float) $this->request->getPost('montant_min');
        $max   = (float) $this->request->getPost('montant_max');
        $frais = (float) $this->request->getPost('montant_frais');

        if ($min >= $max || $min < 0 || $frais < 0) {
            return redirect()->back()->with('error', 'Valeurs invalides.');
        }

        $this->baremeFraisModel->insert([
            'type_operation_id' => $typeOperationId,
            'montant_min'       => $min,
            'montant_max'       => $max,
            'montant_frais'     => $frais,
        ]);
    }

    // ─── Statistiques avec filtre de dates ───

    public function statistiques(int $operateurId)
    {
        $dateDebut = $this->request->getGet('date_debut');
        $dateFin   = $this->request->getGet('date_fin');

        return view('operateur/statistiques', [
            'operateur'    => $this->operateurModel->find($operateurId),
            'stats'        => $this->transactionModel->getStatsOperateur($operateurId, $dateDebut, $dateFin),
            'transactions' => $this->transactionModel->getTransactionsOperateur($operateurId),
        ]);
    }
}
```

**Points clés de l'espace opérateur :**
- **Authentification via `.env`** : pas de table DB pour les admins
- **CRUD complet** des opérateurs et barèmes
- **Dashboard agrégé** : métriques combinées de tous les opérateurs
- **Statistiques filtrées** par période avec analyse des gains par type

---

## 5. Vues (Views)

### 5.1 Architecture des templates

```
app/Views/
├── layouts/
│   ├── header.php      # Navbar responsive + flash messages + Bootstrap
│   └── footer.php      # Footer + JS Bootstrap + auto-dismiss alerts
├── client/
│   ├── login.php       # Connexion par téléphone avec préfixes
│   ├── dashboard.php   # Carte solde + actions rapides + dernières transactions
│   ├── depot.php       # Formulaire de dépôt
│   ├── retrait.php     # Formulaire + tableau des barèmes
│   ├── transfert.php   # Envoi vers un ou plusieurs numéros
│   └── historique.php  # Tableau paginé (20/page)
├── operateur/
│   ├── login.php       # Formulaire admin avec toggle password
│   ├── dashboard.php   # KPIs : transactions, volume, gains par opérateur
│   ├── creer.php       # Création d'opérateur
│   ├── editer.php      # Modification avec commission sortante
│   ├── config.php      # Commission sortante + mapping préfixes autres opérateurs
│   ├── types.php       # Types d'opérations avec barèmes
│   ├── baremes.php     # Édition des tranches de frais
│   ├── statistiques.php# KPIs gains, filtre dates, 50 dernières transactions
│   └── clients.php     # Comptes clients groupés par opérateur
└── home.php            # Page d'accueil avec hero + cards
```

### 5.2 Exemple : Dashboard client

```php
<?php // app/Views/client/dashboard.php ?>
<?= view('layouts/header', ['title' => 'Mon Dashboard']) ?>

<div class="container py-4">
    <!-- Carte solde avec gradient -->
    <div class="card gradient-card mb-4">
        <div class="card-body text-center text-white py-5">
            <p class="mb-1 opacity-75">Solde disponible</p>
            <h2 class="display-4 fw-bold mb-0">
                <?= number_format($utilisateur['solde'], 0, ',', ' ') ?> Ar
            </h2>
        </div>
    </div>

    <!-- 4 boutons d'action rapide -->
    <div class="row g-3 mb-4">
        <?php foreach ([
            ['depot', 'blue', 'Déposer'],
            ['retrait', 'orange', 'Retirer'],
            ['transfert', 'green', 'Transférer'],
            ['historique', 'purple', 'Historique']
        ] as [$url, $color, $label]): ?>
            <div class="col-3">
                <a href="<?= base_url("client/$url") ?>"
                   class="btn btn-<?= $color ?> w-100">
                    <i class="bi bi-<?= $icon ?> fs-3 d-block"></i>
                    <span><?= $label ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 5 dernières transactions -->
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Dernières transactions</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Frais</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieres as $tx): ?>
                    <tr>
                        <td><code><?= $tx['reference_transaction'] ?></code></td>
                        <td><?= ucfirst($tx['nom_operation']) ?></td>
                        <td><?= number_format($tx['montant'], 0, ',', ' ') ?> Ar</td>
                        <td><?= number_format($tx['frais'], 0, ',', ' ') ?> Ar</td>
                        <td><?= date('d/m/Y H:i', strtotime($tx['date_creation'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
```

---

## 6. Filtres d'authentification

Les filtres CI4 protègent les routes des espaces client et opérateur.

```php
<?php
// app/Filters/ClientAuth.php
class ClientAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('user_type') !== 'client') {
            return redirect()->to(base_url('client'))
                ->with('error', 'Veuillez vous connecter.');
        }
    }
}

// app/Filters/OperateurAuth.php
class OperateurAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('user_type') !== 'operator') {
            return redirect()->to(base_url('operateur'))
                ->with('error', 'Accès réservé aux opérateurs.');
        }
    }
}
```

**Enregistrement dans `app/Config/Filters.php` :**
```php
public $aliases = [
    'clientAuth'    => \App\Filters\ClientAuth::class,
    'operateurAuth' => \App\Filters\OperateurAuth::class,
];
```

---

## 7. Routes

```php
<?php
// app/Config/Routes.php

$routes->get('/', 'Home::index');

// ═══════════════════════════════════════
//  ESPACE CLIENT
// ═══════════════════════════════════════
$routes->get('client',          'Client::index');
$routes->post('client/login',   'Client::login');
$routes->get('client/logout',   'Client::logout');

$routes->group('client', ['filter' => 'clientAuth'], function ($routes) {
    $routes->get('dashboard',              'Client::dashboard');
    $routes->match(['get', 'post'], 'depot',      'Client::depot');
    $routes->match(['get', 'post'], 'retrait',    'Client::retrait');
    $routes->match(['get', 'post'], 'transfert',  'Client::transfert');
    $routes->get('historique',             'Client::historique');
});

// ═══════════════════════════════════════
//  ESPACE OPÉRATEUR
// ═══════════════════════════════════════
$routes->get('operateur',         'Operateur::index');
$routes->post('operateur/login',  'Operateur::login');
$routes->get('operateur/logout',  'Operateur::logout');

$routes->group('operateur', ['filter' => 'operateurAuth'], function ($routes) {
    $routes->get('dashboard',                     'Operateur::dashboard');
    $routes->get('config',                        'Operateur::config');
    $routes->match(['get', 'post'], 'creer',       'Operateur::creer');
    $routes->match(['get', 'post'], 'editer/(:num)', 'Operateur::editer/$1');
    $routes->get('types/(:num)',                  'Operateur::types/$1');
    $routes->match(['get', 'post'], 'baremes/(:num)', 'Operateur::baremes/$1');
    $routes->post('ajouterBareme/(:num)',         'Operateur::ajouterBareme/$1');
    $routes->get('supprimerBareme/(:num)',        'Operateur::supprimerBareme/$1');
    $routes->get('statistiques/(:num)',           'Operateur::statistiques/$1');
    $routes->get('clients',                       'Operateur::clients');
});
```

**Structure des URL :**

| URL | Méthode | Description |
|-----|---------|-------------|
| `/` | GET | Page d'accueil |
| `/client` | GET | Formulaire de connexion client |
| `/client/login` | POST | Connexion automatique |
| `/client/dashboard` | GET | Tableau de bord client |
| `/client/depot` | GET/POST | Dépôt d'argent |
| `/client/retrait` | GET/POST | Retrait d'argent |
| `/client/transfert` | GET/POST | Transfert d'argent |
| `/client/historique` | GET | Historique paginé |
| `/operateur` | GET | Formulaire de connexion admin |
| `/operateur/login` | POST | Connexion admin |
| `/operateur/dashboard` | GET | Tableau de bord opérateur |
| `/operateur/creer` | GET/POST | Créer un opérateur |
| `/operateur/config` | GET/POST | Configuration (commission, mapping) |
| `/operateur/editer/{id}` | GET/POST | Modifier un opérateur |
| `/operateur/types/{id}` | GET | Types d'opérations |
| `/operateur/baremes/{id}` | GET/POST | Éditer les barèmes |
| `/operateur/statistiques/{id}` | GET | Statistiques avec filtre dates |
| `/operateur/clients` | GET | Liste des clients |

---

## 8. Logique métier

### 8.1 Règles de calcul

| Opération | Formule | Détail |
|-----------|---------|--------|
| **Dépôt** | `solde += montant` | Frais = 0 Ar |
| **Retrait** | `solde -= (montant + frais)` | Frais selon barème tranche |
| **Transfert interne** | `expéditeur -= (montant + frais)`<br>`destinataire += montant` | Même opérateur |
| **Transfert externe** | `expéditeur -= (montant + frais + commission%)`<br>`destinataire += montant` | Commission = % configurable |

### 8.2 Validation

```
⚠ Montant minimum : 100 Ar (toutes opérations)
⚠ Solde vérifié avant retrait et transfert
⚠ Auto-transfert interdit
⚠ Envois multiples : même opérateur requis
```

### 8.3 Barèmes par défaut (retrait et transfert)

| Tranche (Ar) | Frais (Ar) |
|--------------|------------|
| 100 — 1 000 | 50 |
| 1 001 — 5 000 | 50 |
| 5 001 — 10 000 | 100 |
| 10 001 — 25 000 | 200 |
| 25 001 — 50 000 | 400 |
| 50 001 — 100 000 | 800 |
| 100 001 — 250 000 | 1 500 |
| 250 001 — 500 000 | 1 500 |
| 500 001 — 1 000 000 | 2 500 |
| 1 000 001 — 2 000 000 | 3 000 |

> Les frais de dépôt sont toujours **0 Ar**.

### 8.4 Sécurité

- **Sessions PHP** avec identifiant `user_type` (client vs operator)
- **Filtres CI4** avant chaque groupe de routes protégées
- **Aucun mot de passe stocké** pour les clients (login par téléphone uniquement)
- **Hash bcrypt** pour les mots de passe opérateurs
- **Validation côté serveur** pour toutes les opérations (montant min, solde, etc.)
- **Traçabilité** via `historique_soldes` (solde avant/après chaque transaction)

---

## 9. Installation & Déploiement

### Prérequis

```bash
PHP >= 8.1
Composer
SQLite3 (extension php-sqlite3)
```

### Installation

```bash
# 1. Cloner le projet
git clone <url-du-repo> projet_final_S4
cd projet_final_S4

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env
# Configurer les identifiants opérateur dans .env :
# operator.username=admin
# operator.password=admin

# 4. Créer la base de données
php spark migrate

# 5. Insérer les données de démonstration
php spark db:seed DatabaseSeeder

# 6. Lancer le serveur de développement
php spark serve
```

### Accès

| Espace | URL | Identifiants |
|--------|-----|--------------|
| Accueil | `http://localhost:8080/` | — |
| Client | `http://localhost:8080/client` | Entrer un numéro commençant par 034, 033 ou 032 |
| Opérateur | `http://localhost:8080/operateur` | admin / admin (configurable dans `.env`) |

### Commandes utiles

```bash
# Recréer la base depuis zéro
php spark migrate:refresh --seed DatabaseSeeder

# Voir les routes disponibles
php spark routes
```

---

## Conclusion

Cette application Mobile Money implémente les fonctionnalités suivantes :

✅ **Espace client complet** : connexion automatique, dépôt, retrait, transfert (simple & multiple), historique paginé  
✅ **Espace administration** : gestion des opérateurs, barèmes de frais configurables, statistiques par période  
✅ **Transferts inter-opérateurs** avec commission configurable  
✅ **Multi-prefixes** (034, 038) pour un même opérateur  
✅ **Traçabilité** complète via historique des soldes  
✅ **Architecture MVC** propre avec CodeIgniter 4  
✅ **Base SQLite** sans configuration serveur (fichier unique)  
✅ **Interface responsive** avec Bootstrap 5

