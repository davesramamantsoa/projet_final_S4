# Système Mobile Money - Documentation Finale

## 🎯 Concept

Application de gestion de mobile money pour **UN SEUL opérateur principal** (Telma) qui peut gérer des transferts vers d'autres opérateurs partenaires (Airtel, Orange).

## 🏗️ Architecture

### Opérateur Principal (Telma)
- Dashboard unifié
- Gestion des clients
- Configuration des barèmes
- Statistiques complètes
- Configuration des opérateurs partenaires

### Opérateurs Partenaires (Airtel, Orange)
- Configurables pour recevoir des transferts
- Commission configurable sur transferts entrants
- Préfixes configurables
- Pas de dashboard séparé

## 🔐 Authentification

### Opérateur
```
URL : /operateur
Identifiants : admin / admin
```

### Client
```
URL : /client
Méthode : Login par numéro de téléphone uniquement
Restriction : Seuls les numéros avec préfixes de Telma (034, 038) acceptés
```

**Important :** Si vous changez les préfixes de Telma dans la configuration, le login client s'adapte automatiquement.

## 📋 Fonctionnalités Complètes

### Côté Opérateur

#### 1. Dashboard (`/operateur/dashboard`)
- **KPIs Globaux :**
  - Total transactions
  - Volume total (Ar)
  - Gains retrait
  - Total gains

- **Situation des Gains :**
  - Gains Retrait
  - Gains Transfert (Même opérateur) ← Telma → Telma
  - Gains Transfert (Autres opérateurs) ← Telma → Airtel/Orange
  - Commissions reçues ← Airtel/Orange → Telma
  - Total calculé

- **Montants à Envoyer :**
  - Par opérateur externe
  - Avec pourcentage du total
  - Total global

#### 2. Configuration (`/operateur/config`)
- **Mon Opérateur (Telma) :**
  - Nom
  - Préfixes (ex: 034, 038)
  
- **Autres Opérateurs :**
  - Préfixes de chaque opérateur
  - Commission % sur transferts entrants
  - Ajout/modification d'opérateurs

#### 3. Gestion des Barèmes (`/operateur/types/{id}`)
- Configuration par type d'opération (dépôt, retrait, transfert)
- Barèmes par tranche de montant
- Modification/ajout/suppression de tranches

#### 4. Comptes Clients (`/operateur/clients`)
- Liste des clients avec préfixes de Telma uniquement
- Numéro, solde, date d'inscription
- Total des soldes

#### 5. Statistiques (`/operateur/statistiques/{id}`)
- Statistiques détaillées
- Filtres par date
- Historique des transactions

### Côté Client

#### 1. Dashboard (`/client/dashboard`)
- Solde actuel
- Dernières transactions
- Accès rapide aux opérations

#### 2. Dépôt (`/client/depot`)
- Montant minimum : 100 Ar
- Pas de frais
- Crédité instantanément

#### 3. Retrait (`/client/retrait`)
- Frais dynamiques selon barème de Telma
- Affichage en temps réel des frais
- Tableau des tranches
- Débité du solde

#### 4. Transfert (`/client/transfert`)
##### Transfert Simple
- Vers un numéro
- Détection automatique de l'opérateur destinataire
- Frais selon le barème

##### Transfert Multiple
- Vers plusieurs numéros (séparés par virgule)
- **Restriction : même opérateur uniquement**
- Montant divisé équitablement
- Validation automatique

##### Option "Inclure frais de retrait"
- Disponible pour transferts externes uniquement
- Vous payez les frais de retrait du destinataire
- Le destinataire reçoit le montant + frais de retrait

##### Calcul des Frais
```
Transfert Interne (Telma → Telma) :
  Total débité = Montant + Frais transfert

Transfert Externe (Telma → Airtel) :
  Total débité = Montant + Frais transfert + Commission externe + Frais retrait (si cochée)
  Montant reçu = Montant + Frais retrait (si cochée)
```

#### 5. Historique (`/client/historique`)
- **Bidirectionnel :** voit les envois ET les réceptions
- **Badge ENVOI** : rouge avec flèche haut
- **Badge REÇU** : vert avec flèche bas
- Affichage du destinataire ou de l'expéditeur
- Pagination

## 🧮 Logique des Transactions

### Transfert Interne (Telma → Telma)
```
Expéditeur débité : Montant + Frais transfert
Destinataire crédité : Montant
Telma gagne : Frais transfert
```

### Transfert Externe (Telma → Airtel)
```
Expéditeur débité : Montant + Frais transfert + Commission Airtel
Destinataire crédité : Montant
Telma gagne : Frais transfert
Airtel gagne : Commission (montant × %)
```

### Transfert Externe avec Frais Retrait
```
Expéditeur débité : Montant + Frais transfert + Commission Airtel + Frais retrait Airtel
Destinataire crédité : Montant + Frais retrait
Telma gagne : Frais transfert
Airtel gagne : Commission (montant × %)
```

### Transactions Créées
- **