# Répartition des Tâches — Projet Final S4
## Application Mobile Money — CodeIgniter 4 + SQLite

**Binôme :**
| # | Nom | Rôle principal |
|---|-----|----------------|
| 1 | **Mirindra** | Base de données · Modèles · Logique métier |
| 2 | **Dave** | Contrôleurs · Vues · Design UI |

---

## Étudiant 1 — Mirindra

### Base de données & Schéma
- [ok] Conception du schéma relationnel SQLite en français
- [ok] Création du fichier `database.sql` avec les 6 tables :
  - `utilisateurs` — login automatique par numéro de téléphone
  - `operateurs` — gestion des préfixes (034, 033, 032…)
  - `types_operations` — depot / retrait / transfert par opérateur
  - `baremes_frais` — tranches de montants avec frais associés
  - `transactions` — enregistrement de toutes les opérations
  - `historique_soldes` — traçabilité des mouvements de solde
- [ok] Création de la migration CI4 : `2024-01-01-000001_CreerToutesLesTables.php`
- [ok] Ajout des index de performance (telephone, date, utilisateur)

### Seeder & Données de démonstration
- [ok] `DatabaseSeeder.php` — données initiales complètes :
  - 3 opérateurs : Telma (034), Airtel (033), Orange (032)
  - Barèmes par défaut pour tous les types d'opérations
  - 4 clients de démonstration avec soldes préchargés
  - 9 transactions de démonstration (dépôts, retraits, transferts)

### Modèles (app/Models/)
- [ok] `UtilisateurModel.php`
  - `creerOuGetUtilisateur()` — auto-inscription sans mot de passe
  - `mettreAJourSolde()` — crédit / débit avec vérification solde
  - `getUtilisateursByPrefixe()` — situation comptes clients par opérateur
- [ok] `OperateurModel.php`
  - `detecterParTelephone()` — détection opérateur par préfixe du numéro
  - `prefixeExiste()` — validation unicité préfixe
  - `creerOperateur()` — création sécurisée
- [ok] `TypeOperationModel.php`
  - `getByOperateurEtType()` — récupération ciblée du type
  - `creerTypesParDefaut()` — initialisation automatique des 3 types
- [ok] `BaremeFraisModel.php`
  - `calculerFrais()` — calcul automatique selon la tranche de montant
  - `creerBaremesParDefaut()` — barèmes du sujet (frais 0 pour dépôt)
- [ok] `TransactionModel.php`
  - `creerTransaction()` — enregistrement avec référence unique
  - `getTransactionsUtilisateur()` — JOIN multi-tables (transaction → type → opérateur)
  - `getTransactionsOperateur()` — vue complète pour l'espace opérateur
  - `getStatsOperateur()` — calcul gains retrait + transfert avec filtre dates
  - `genererReference()` — référence unique `TXN` + hex + timestamp
- [ok] `HistoriqueSoldeModel.php`
  - `enregistrer()` — traçabilité solde avant / après chaque opération

### Logique métier
- [ok] Règle dépôt : `solde += montant` (frais = 0 Ar)
- [ok] Règle retrait : `solde -= (montant + frais)`
- [ok] Règle transfert : `expéditeur -= (montant + frais)` / `destinataire += montant`
- [ok] Validation montant minimum 100 Ar sur toutes les opérations
- [ok] Vérification solde suffisant avant retrait et transfert

### Configuration
- [ok] `app/Config/Database.php` — connexion SQLite3 (`WRITEPATH/database/money.db`)
- [ok] `app/Config/Paths.php` — chemins CI4
- [ok] `app/Config/Filters.php` — enregistrement des filtres d'authentification
- [ok] `app/Filters/ClientAuth.php` — protection routes espace client
- [ok] `app/Filters/OperateurAuth.php` — protection routes espace opérateur

---

## Étudiant 2 — Dave

### Contrôleurs (app/Controllers/)
- [x] `BaseController.php` — classe de base, chargement helpers (`url`, `form`, `text`)
- [x] `Home.php` — page d'accueil
- [x] `Client.php` — espace client complet :
  - `index()` / `login()` / `logout()` — connexion automatique par téléphone
  - `dashboard()` — solde + 5 dernières transactions
  - `depot()` — dépôt automatique avec historique solde
  - `retrait()` — retrait avec calcul frais et vérification solde
  - `transfert()` — transfert inter-comptes avec création auto du destinataire
  - `historique()` — pagination des transactions (20 par page)
  - **Fix CI4 4.4+** : remplacement de `getMethod() === 'get'` par `is('get')`
- [x] `Operateur.php` — espace administration complet :
  - `index()` / `login()` / `logout()` — auth via `.env` (sans table DB)
  - `dashboard()` — vue globale de tous les opérateurs + métriques
  - `creer()` — création d'un opérateur avec préfixe + types par défaut
  - `types()` — vue des 3 types d'opérations avec leurs barèmes
  - `baremes()` — modification des frais par tranche
  - `ajouterBareme()` / `supprimerBareme()` — gestion CRUD des barèmes
  - `statistiques()` — gains retrait / transfert avec filtre de dates
  - `clients()` — situation des comptes clients par opérateur

### Routes (app/Config/Routes.php)
- [x] Routes espace client avec filtre `clientAuth`
- [x] Routes espace opérateur avec filtre `operateurAuth`
- [x] Routes groupées CI4 pour protéger les pages authentifiées

### Vues — Espace Client (app/Views/client/)
- [x] `login.php` — connexion automatique par numéro, préfixes affichés
- [x] `dashboard.php` — carte solde gradient + 4 boutons actions + tableau transactions
- [x] `depot.php` — formulaire opérateur + montant, info frais = 0
- [x] `retrait.php` — formulaire avec tableau barèmes indicatif, solde affiché
- [x] `transfert.php` — formulaire numéro destinataire + montant + opérateur
- [x] `historique.php` — tableau complet avec pagination

### Vues — Espace Opérateur (app/Views/operateur/)
- [x] `login.php` — connexion admin avec toggle mot de passe
- [x] `dashboard.php` — métriques par opérateur : transactions, volume, gains retrait/transfert
- [x] `creer.php` — formulaire création opérateur + préfixe
- [x] `types.php` — tableau des 3 types avec leurs barèmes par opérateur
- [x] `baremes.php` — tableau éditable des frais + ajout / suppression de tranche
- [x] `statistiques.php` — KPIs gains, filtre dates, tableau 50 dernières transactions
- [x] `clients.php` — comptes clients groupés par opérateur avec total soldes

### Vues — Layouts (app/Views/layouts/)
- [x] `header.php` — navbar responsive 3 états (anonyme / client / opérateur), flashdata
- [x] `footer.php` — footer sombre, Bootstrap JS

### Page d'accueil (app/Views/home.php)
- [x] Hero section avec fond dégradé teal-nuit
- [x] Deux cards glassmorphism (Espace Client / Espace Opérateur)
- [x] Liste des opérateurs disponibles avec préfixes colorés


