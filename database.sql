-- ------------------------------------------------------------
-- 1. TABLE DES UTILISATEURS (Simplifiée pour login auto)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_telephone  TEXT UNIQUE NOT NULL,
    solde             REAL DEFAULT 0,
    date_creation     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. TABLE DES OPERATEURS (Telma, Orange, Airtel...)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS operateurs (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    nom_operateur      TEXT NOT NULL,
    prefixe_operateur  TEXT UNIQUE NOT NULL
);

-- ------------------------------------------------------------
-- 3. TABLE DES TYPES D'OPERATIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS types_operations (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id    INTEGER NOT NULL,
    nom_operation   TEXT NOT NULL CHECK(nom_operation IN ('depot', 'retrait', 'transfert')),
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 4. TABLE DES BAREMES DE FRAIS (Par tranche de montant)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS baremes_frais (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id   INTEGER NOT NULL,
    montant_min         REAL NOT NULL,
    montant_max         REAL NOT NULL,
    montant_frais       REAL NOT NULL,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 5. TABLE DES TRANSACTIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    reference_transaction   TEXT UNIQUE NOT NULL,
    utilisateur_id          INTEGER NOT NULL,
    type_operation_id       INTEGER NOT NULL,
    montant                 REAL NOT NULL,
    frais                   REAL DEFAULT 0,
    telephone_destinataire  TEXT,
    date_creation           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id)    REFERENCES utilisateurs(id),
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id)
);

-- ------------------------------------------------------------
-- 6. TABLE DE L'HISTORIQUE DES SOLDES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS historique_soldes (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id    INTEGER NOT NULL,
    transaction_id    INTEGER NOT NULL,
    solde_precedent   REAL NOT NULL,
    nouveau_solde     REAL NOT NULL,
    date_creation     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id)  REFERENCES utilisateurs(id),
    FOREIGN KEY (transaction_id)  REFERENCES transactions(id)
);

-- ------------------------------------------------------------
-- INDEX
-- ------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_util_tel    ON utilisateurs(numero_telephone);
CREATE INDEX IF NOT EXISTS idx_trans_util  ON transactions(utilisateur_id);
CREATE INDEX IF NOT EXISTS idx_trans_type  ON transactions(type_operation_id);
CREATE INDEX IF NOT EXISTS idx_trans_date  ON transactions(date_creation);
CREATE INDEX IF NOT EXISTS idx_hist_util   ON historique_soldes(utilisateur_id);

-- ------------------------------------------------------------
-- DONNEES INITIALES (Operateurs + barèmes)
-- ------------------------------------------------------------

-- Opérateurs
INSERT OR IGNORE INTO operateurs (nom_operateur, prefixe_operateur) VALUES
    ('Telma',  '034'),
    ('Airtel', '033'),
    ('Orange', '032');

-- Types d'opérations pour Telma (id=1)
INSERT OR IGNORE INTO types_operations (operateur_id, nom_operation) VALUES
    (1, 'depot'), (1, 'retrait'), (1, 'transfert');

-- Types d'opérations pour Airtel (id=2)
INSERT OR IGNORE INTO types_operations (operateur_id, nom_operation) VALUES
    (2, 'depot'), (2, 'retrait'), (2, 'transfert');

-- Types d'opérations pour Orange (id=3)
INSERT OR IGNORE INTO types_operations (operateur_id, nom_operation) VALUES
    (3, 'depot'), (3, 'retrait'), (3, 'transfert');

-- Barèmes retrait Telma (type_operation_id=2)
INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, montant_frais) VALUES
    (2, 100, 1000, 50), (2, 1001, 5000, 50), (2, 5001, 10000, 100),
    (2, 10001, 25000, 200), (2, 25001, 50000, 400), (2, 50001, 100000, 800),
    (2, 100001, 250000, 1500), (2, 250001, 500000, 1500),
    (2, 500001, 1000000, 2500), (2, 1000001, 2000000, 3000);

-- Barèmes transfert Telma (type_operation_id=3)
INSERT OR IGNORE INTO baremes_frais (type_operation_id, montant_min, montant_max, montant_frais) VALUES
    (3, 100, 1000, 50), (3, 1001, 5000, 50), (3, 5001, 10000, 100),
    (3, 10001, 25000, 200), (3, 25001, 50000, 400), (3, 50001, 100000, 800),
    (3, 100001, 250000, 1500), (3, 250001, 500000, 1500),
    (3, 500001, 1000000, 2500), (3, 1000001, 2000000, 3000);

-- (Répéter pour Airtel ids 5,6 et Orange ids 8,9 — les frais dépôt restent à 0)
