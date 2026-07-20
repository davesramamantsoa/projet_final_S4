<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\BaremeFraisModel;
use App\Models\TransactionModel;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $baremeFraisModel = new BaremeFraisModel();
        $transactionModel = new TransactionModel();

        // ─── 1. Opérateurs ────────────────────────────────
        $operateurs = [
            ['nom_operateur' => 'Telma',  'prefixe_operateur' => '034, 038', 'commission_transfert_externe' => 2.0, 'username' => 'telma', 'password' => password_hash('telma123', PASSWORD_DEFAULT)],
            ['nom_operateur' => 'Airtel', 'prefixe_operateur' => '033', 'commission_transfert_externe' => 1.5, 'username' => 'airtel', 'password' => password_hash('airtel123', PASSWORD_DEFAULT)],
            ['nom_operateur' => 'Orange', 'prefixe_operateur' => '032, 031', 'commission_transfert_externe' => 3.0, 'username' => 'orange', 'password' => password_hash('orange123', PASSWORD_DEFAULT)],
        ];

        $operateurIds = [];
        foreach ($operateurs as $opData) {
            $existant = $this->db->table('operateurs')
                ->where('nom_operateur', $opData['nom_operateur'])
                ->get()->getRowArray();

            if ($existant) {
                $operateurIds[$opData['nom_operateur']] = $existant['id'];
                continue;
            }

            $this->db->table('operateurs')->insert($opData);
            $opId = $this->db->insertID();
            $operateurIds[$opData['nom_operateur']] = $opId;

            foreach (['depot', 'retrait', 'transfert'] as $nomOperation) {
                $this->db->table('types_operations')->insert([
                    'operateur_id'  => $opId,
                    'nom_operation' => $nomOperation,
                ]);
                $typeId = $this->db->insertID();
                $baremeFraisModel->creerBaremesParDefaut($typeId, $nomOperation);
            }

            echo "Opérateur créé : {$opData['nom_operateur']} (préfixe {$opData['prefixe_operateur']})\n";
        }

        // ─── 2. Clients de démonstration ────────────────────────────
        $clients = [
            ['numero_telephone' => '0340001234', 'solde' => 125000],
            ['numero_telephone' => '0340005678', 'solde' => 45000],
            ['numero_telephone' => '0330009876', 'solde' => 78500],
            ['numero_telephone' => '0320001111', 'solde' => 200000],
        ];

        $clientIds = [];
        foreach ($clients as $clientData) {
            $existant = $this->db->table('utilisateurs')
                ->where('numero_telephone', $clientData['numero_telephone'])
                ->get()->getRowArray();

            if ($existant) {
                $clientIds[$clientData['numero_telephone']] = $existant['id'];
                continue;
            }

            $this->db->table('utilisateurs')->insert([
                'numero_telephone' => $clientData['numero_telephone'],
                'solde'            => $clientData['solde'],
                'date_creation'    => date('Y-m-d H:i:s', strtotime('-' . rand(5, 60) . ' days')),
            ]);
            $clientIds[$clientData['numero_telephone']] = $this->db->insertID();
        }
        echo count($clients) . " clients de démonstration créés\n";

        // ─── 3. Transactions de démonstration ───────────────────────
        // Récupérer les IDs des types d'opérations pour Telma
        $telmaId    = $operateurIds['Telma'];
        $airtelId   = $operateurIds['Airtel'];

        $typeDepotTelma     = $this->db->table('types_operations')
            ->where('operateur_id', $telmaId)->where('nom_operation', 'depot')->get()->getRowArray();
        $typeRetraitTelma   = $this->db->table('types_operations')
            ->where('operateur_id', $telmaId)->where('nom_operation', 'retrait')->get()->getRowArray();
        $typeTransfertTelma = $this->db->table('types_operations')
            ->where('operateur_id', $telmaId)->where('nom_operation', 'transfert')->get()->getRowArray();
        $typeDepotAirtel    = $this->db->table('types_operations')
            ->where('operateur_id', $airtelId)->where('nom_operation', 'depot')->get()->getRowArray();

        if (!$typeDepotTelma || !$typeRetraitTelma || !$typeTransfertTelma) {
            echo "Types d'opérations Telma introuvables, skip transactions\n";
        } else {
            $demoTransactions = [
                // Client 034-0001234 — dépôts
                ['utilisateur_id' => $clientIds['0340001234'], 'type_operation_id' => $typeDepotTelma['id'],
                 'montant' => 50000,  'frais' => 0, 'telephone_destinataire' => null,          'jours' => 30],
                ['utilisateur_id' => $clientIds['0340001234'], 'type_operation_id' => $typeDepotTelma['id'],
                 'montant' => 100000, 'frais' => 0, 'telephone_destinataire' => null,          'jours' => 20],
                // retraits
                ['utilisateur_id' => $clientIds['0340001234'], 'type_operation_id' => $typeRetraitTelma['id'],
                 'montant' => 10000, 'frais' => 200, 'telephone_destinataire' => null,         'jours' => 15],
                ['utilisateur_id' => $clientIds['0340001234'], 'type_operation_id' => $typeRetraitTelma['id'],
                 'montant' => 25000, 'frais' => 400, 'telephone_destinataire' => null,         'jours' => 10],
                // transferts
                ['utilisateur_id' => $clientIds['0340001234'], 'type_operation_id' => $typeTransfertTelma['id'],
                 'montant' => 5000,  'frais' => 50,  'telephone_destinataire' => '0340005678', 'jours' => 8],
                ['utilisateur_id' => $clientIds['0340001234'], 'type_operation_id' => $typeTransfertTelma['id'],
                 'montant' => 15000, 'frais' => 200, 'telephone_destinataire' => '0330009876', 'jours' => 5],
                // Client 034-0005678
                ['utilisateur_id' => $clientIds['0340005678'], 'type_operation_id' => $typeDepotTelma['id'],
                 'montant' => 50000, 'frais' => 0, 'telephone_destinataire' => null,           'jours' => 12],
                ['utilisateur_id' => $clientIds['0340005678'], 'type_operation_id' => $typeRetraitTelma['id'],
                 'montant' => 5000,  'frais' => 50, 'telephone_destinataire' => null,          'jours' => 3],
                // Client Airtel 033-0009876
                ['utilisateur_id' => $clientIds['0330009876'], 'type_operation_id' => $typeDepotAirtel['id'],
                 'montant' => 100000,'frais' => 0, 'telephone_destinataire' => null,           'jours' => 7],
            ];

            foreach ($demoTransactions as $tx) {
                $date = date('Y-m-d H:i:s', strtotime('-' . $tx['jours'] . ' days'));
                $this->db->table('transactions')->insert([
                    'reference_transaction'  => $transactionModel->genererReference(),
                    'utilisateur_id'         => $tx['utilisateur_id'],
                    'type_operation_id'      => $tx['type_operation_id'],
                    'montant'                => $tx['montant'],
                    'frais'                  => $tx['frais'],
                    'telephone_destinataire' => $tx['telephone_destinataire'],
                    'date_creation'          => $date,
                ]);
            }
            echo count($demoTransactions) . " transactions de démonstration créées\n";
        }

        echo "\n✓ Seeder terminé !\n";
        echo "  Connexions opérateurs :\n";
        echo "    - Telma  : telma / telma123\n";
        echo "    - Airtel : airtel / airtel123\n";
        echo "    - Orange : orange / orange123\n";
        echo "  Clients demo : 0340001234, 0340005678, 0330009876, 0320001111\n";
        echo "  (solde initial chargé — connectez-vous pour tester les opérations)\n";
    }
}
