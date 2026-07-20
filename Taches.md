# Tâches — Projet Final S4
## Application Mobile Money — CodeIgniter 4 + SQLite

**Binôme :**
| # | Nom | Matricule |
|---|-----|-----------|
| 1 | **Mirindra** | 3927 |
| 2 | **Dave** | 4213 |

---

## ✅ Fonctionnalités Principales

### Base de Données
- [x] 6 tables : utilisateurs, operateurs, types_operations, baremes_frais, transactions, historique_soldes
- [x] Support multi-préfixes par opérateur
- [x] Champs username/password pour login séparé par opérateur
- [x] Commission externe configurable par opérateur

### Espace Client
- [x] Connexion automatique par numéro
- [x] Dépôt d'argent (frais = 0)
- [x] Retrait avec calcul frais
- [x] Transfert simple
- [x] **Transfert multiple** (même opérateur uniquement)
- [x] **Option "Inclure frais de retrait"** (autres opérateurs uniquement)
- [x] Historique complet

### Espace Opérateur
- [x] **3 dashboards séparés** (Telma, Airtel, Orange)
- [x] Login avec username/password propre à chaque opérateur
- [x] Configuration multi-préfixes (ex: 034, 038)
- [x] Configuration commission externe (%)
- [x] Statistiques avec séparation des gains :
  - Gains retrait (%)
  - Gains transfert interne (%)
  - Gains transfert externe (%)
- [x] **Montants à envoyer** aux autres opérateurs avec %
- [x] Gestion barèmes de frais
- [x] Liste clients

---

## 💰 Règles de Calcul

### Commission
- **C'est l'opérateur DESTINATAIRE qui prend la commission**
- Exemple : Telma → Orange (1%), commission va à Orange

### Transfert Externe
```
Débité = Montant + Frais transfert + Commission dest. + Frais retrait (si option)
Reçu = Montant (+ Frais retrait si option)
```

### Montants à Envoyer
```
Opérateur expéditeur doit envoyer :
- Le montant transféré
- + Les frais de retrait (si option cochée)
```

### Gains
- **Frais transfert** → Opérateur expéditeur
- **Commission** → Opérateur destinataire

---

## 📁 Structure

```
app/
├── Controllers/
│   ├── Client.php (transfert multiple + frais retrait)
│   └── Operateur.php (login séparé, stats par opérateur)
├── Models/
│   ├── OperateurModel.php (multi-préfixes, username/password)
│   └── TransactionModel.php (séparation gains, montants à envoyer)
└── Views/
    ├── client/transfert.php (envoi multiple, option frais)
    ├── operateur/dashboard.php (dashboard personnel)
    └── operateur/statistiques.php (gains séparés avec %)
```

---

## 🔑 Identifiants

```
Telma  : telma  / telma123
Airtel : airtel / airtel123
Orange : orange / orange123
```

---

## 📝 Documentation

- `README.md` : Installation et présentation
- `CALCULS.md` : Exemples détaillés de calculs
- `database.sql` : Schéma complet
