<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AjouterCommissionTransfertExterne extends Migration
{
    public function up(): void
    {
        // Ajouter la colonne commission_transfert_externe si elle n'existe pas
        if (!$this->db->fieldExists('commission_transfert_externe', 'operateurs')) {
            $fields = [
                'commission_transfert_externe' => [
                    'type' => 'REAL',
                    'default' => 0,
                    'null' => false,
                ],
            ];
            $this->forge->addColumn('operateurs', $fields);
        }
    }

    public function down(): void
    {
        // Supprimer la colonne commission_transfert_externe
        if ($this->db->fieldExists('commission_transfert_externe', 'operateurs')) {
            $this->forge->dropColumn('operateurs', 'commission_transfert_externe');
        }
    }
}
