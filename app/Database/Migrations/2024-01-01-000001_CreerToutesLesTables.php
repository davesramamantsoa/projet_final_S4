<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreerToutesLesTables extends Migration
{
    public function up(): void
    {
        // ─── 1. utilisateurs ───────────────────────────────────────────
        $this->forge->addField([
            'id'               => ['type' => 'INTEGER', 'auto_increment' => true],
            'numero_telephone' => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'solde'            => ['type' => 'REAL',    'default' => 0],
            'date_creation'    => ['type' => 'DATETIME','null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('utilisateurs', true);

        // ─── 2. operateurs ─────────────────────────────────────────────
        $this->forge->addField([
            'id'                 => ['type' => 'INTEGER', 'auto_increment' => true],
            'nom_operateur'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'prefixe_operateur'  => ['type' => 'VARCHAR', 'constraint' => 10, 'unique' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('operateurs', true);

        // ─── 3. types_operations ───────────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'INTEGER', 'auto_increment' => true],
            'operateur_id'  => ['type' => 'INTEGER'],
            'nom_operation' => ['type' => 'VARCHAR', 'constraint' => 20],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('types_operations', true);

        // ─── 4. baremes_frais ──────────────────────────────────────────
        $this->forge->addField([
            'id'                => ['type' => 'INTEGER', 'auto_increment' => true],
            'type_operation_id' => ['type' => 'INTEGER'],
            'montant_min'       => ['type' => 'REAL'],
            'montant_max'       => ['type' => 'REAL'],
            'montant_frais'     => ['type' => 'REAL'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('baremes_frais', true);

        // ─── 5. transactions ───────────────────────────────────────────
        $this->forge->addField([
            'id'                     => ['type' => 'INTEGER', 'auto_increment' => true],
            'reference_transaction'  => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'utilisateur_id'         => ['type' => 'INTEGER'],
            'type_operation_id'      => ['type' => 'INTEGER'],
            'montant'                => ['type' => 'REAL'],
            'frais'                  => ['type' => 'REAL', 'default' => 0],
            'telephone_destinataire' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'date_creation'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('transactions', true);

        // ─── 6. historique_soldes ──────────────────────────────────────
        $this->forge->addField([
            'id'               => ['type' => 'INTEGER', 'auto_increment' => true],
            'utilisateur_id'   => ['type' => 'INTEGER'],
            'transaction_id'   => ['type' => 'INTEGER'],
            'solde_precedent'  => ['type' => 'REAL'],
            'nouveau_solde'    => ['type' => 'REAL'],
            'date_creation'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('historique_soldes', true);

        // ─── Index ─────────────────────────────────────────────────────
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_util_tel ON utilisateurs(numero_telephone)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_trans_util ON transactions(utilisateur_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_trans_type ON transactions(type_operation_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_trans_date ON transactions(date_creation)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_hist_util ON historique_soldes(utilisateur_id)');
    }

    public function down(): void
    {
        $this->forge->dropTable('historique_soldes', true);
        $this->forge->dropTable('transactions',      true);
        $this->forge->dropTable('baremes_frais',     true);
        $this->forge->dropTable('types_operations',  true);
        $this->forge->dropTable('operateurs',        true);
        $this->forge->dropTable('utilisateurs',      true);
    }
}
