## todo list du projet Final
-architecture du projet:
-conception et creation de base de donne:
```sql
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_telephone TEXT UNIQUE NOT NULL,
    solde REAL DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS operateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_operateur TEXT NOT NULL,         
    prefixe_operateur TEXT UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id INTEGER NOT NULL,
    nom_operation TEXT NOT NULL CHECK(nom_operation IN ('depot', 'retrait', 'transfert')),
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS baremes_frais (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL,
    montant_min REAL NOT NULL,
    montant_max REAL NOT NULL,
    montant_frais REAL NOT NULL,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference_transaction TEXT UNIQUE NOT NULL,
    utilisateur_id INTEGER NOT NULL,            
    type_operation_id INTEGER NOT NULL,         
    montant REAL NOT NULL,                      
    frais REAL DEFAULT 0,                       
    telephone_destinataire TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id)
);

CREATE TABLE IF NOT EXISTS historique_soldes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NOT NULL,
    transaction_id INTEGER NOT NULL,
    solde_precedent REAL NOT NULL,
    nouveau_solde REAL NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
);
```
-insertion de donne de test:
    -operateurs(Orange,Telma,Airtel)
    -tranche de baremes de frais
### cote client:
    -page de login automatique
        insertion du numero
    -page de consulation de solde
    -formulaire d operation(depot,transfert,retrait)
## cote operateur:
    -interface de configuration de prefixe valable de l operateur
    -CRUD des baremes avec frais
    -Calcul la situation des gains (Somme des frais perçus)
    -Affichage la situation globale des comptes clients


