# 📱 Mobile Money — Projet Final S4

Application de gestion d'argent mobile (Mobile Money) développée avec **CodeIgniter 4**, **SQLite** et **Bootstrap 5**.

## 🚀 Installation

```bash
# 1. Installer les dépendances
composer install

# 2. Créer la base de données
php spark migrate

# 3. Insérer les données initiales
php spark db:seed DatabaseSeeder

# 4. Lancer le serveur
php spark serve
```

## 🔐 Connexion

### Opérateurs (3 dashboards séparés)
- **Telma** : `telma` / `telma123`
- **Airtel** : `airtel` / `airtel123`  
- **Orange** : `orange` / `orange123`
- **Créer un nouvel opérateur** : Cliquer sur "Créer un nouvel opérateur" sur la page de login

### Clients
N'importe quel numéro (création automatique) : `0340001234`, `0331234567`, etc.

## ✨ Fonctionnalités

### Côté Opérateur
- ✅ Dashboard personnel par opérateur
- ✅ Multi-préfixes (ex: 034, 038 pour Telma)
- ✅ Configuration commission transfert externe (%)
- ✅ Séparation gains : retrait / transfert interne / transfert externe
- ✅ Affichage des % pour chaque type de gain
- ✅ Montants à envoyer aux autres opérateurs avec %
- ✅ Gestion barèmes de frais

### Côté Client  
- ✅ Transfert simple ou multiple (même opérateur uniquement)
- ✅ Option "inclure frais de retrait" (autres opérateurs uniquement)
- ✅ Dépôt / Retrait avec calcul automatique des frais
- ✅ Historique des transactions

## 💰 Calculs des Frais

### Transfert vers autre opérateur
```
Exemple : Telma (034) → Orange (032)
Orange a commission = 1%

Montant envoyé : 2000 Ar
Frais transfert Telma : 50 Ar (selon barème)
Commission Orange : 2000 × 1% = 20 Ar

Total débité : 2000 + 50 + 20 = 2070 Ar
Destinataire reçoit : 2000 Ar
```

### Avec option "Inclure frais retrait"
```
Montant : 2000 Ar
Frais transfert : 50 Ar
Commission Orange : 20 Ar
Frais retrait Orange : 50 Ar

Total débité : 2000 + 50 + 20 + 50 = 2120 Ar
Destinataire reçoit : 2050 Ar (peut retirer 2000 Ar sans frais)
```

### Envoi multiple
```
3 numéros du même opérateur
Montant total : 30000 Ar

Chaque destinataire reçoit : 10000 Ar
Frais calculés pour chaque portion
```

## 📊 Structure

```
app/
├── Controllers/
│   ├── Client.php      # Logique client
│   └── Operateur.php   # Logique opérateur
├── Models/
│   ├── OperateurModel.php
│   ├── TransactionModel.php
│   └── ...
└── Views/
    ├── client/         # Vues client
    └── operateur/      # Vues opérateur
```

## 🎯 Points Clés

1. **3 dashboards séparés** : Chaque opérateur a son propre compte
2. **Commission** : C'est l'opérateur DESTINATAIRE qui prend la commission
3. **Montants à envoyer** : Montant transféré + frais retrait (si option cochée)
4. **Envoi multiple** : Même opérateur uniquement
5. **Frais retrait** : Autres opérateurs uniquement

---

Projet S4 - Mirindra (3927) & Dave (4213)
