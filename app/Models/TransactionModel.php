<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table         = 'transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'reference_transaction', 'utilisateur_id', 'type_operation_id',
        'montant', 'frais', 'telephone_destinataire', 'montant_a_envoyer', 'commission_externe',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'date_creation';
    protected $updatedField  = '';
    protected $deletedField  = '';

    public function genererReference(): string
    {
        return 'TXN' . strtoupper(bin2hex(random_bytes(5))) . time();
    }

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

    public function getTransactionsUtilisateur(int $utilisateurId, int $limit = 50, int $offset = 0): array
    {
        // Récupérer le numéro de téléphone de l'utilisateur
        $userModel = new \App\Models\UtilisateurModel();
        $user = $userModel->find($utilisateurId);
        $monNumero = $user['numero_telephone'] ?? '';
        
        // Récupérer transactions où je suis l'expéditeur OU le destinataire
        $builder = $this->db->table('transactions t')
            ->select('t.*, type_op.nom_operation, op.nom_operateur, op.prefixe_operateur')
            ->join('types_operations type_op', 't.type_operation_id = type_op.id')
            ->join('operateurs op', 'type_op.operateur_id = op.id')
            ->groupStart()
                ->where('t.utilisateur_id', $utilisateurId)
                ->orWhere('t.telephone_destinataire', $monNumero)
            ->groupEnd()
            ->orderBy('t.date_creation', 'DESC')
            ->limit($limit, $offset);
        
        return $builder->get()->getResultArray();
    }

    public function countTransactionsUtilisateur(int $utilisateurId): int
    {
        return (int) $this->db->table('transactions')
            ->where('utilisateur_id', $utilisateurId)
            ->countAllResults();
    }

    public function getTransactionsOperateur(int $operateurId, int $limit = 50): array
    {
        return $this->db->table('transactions t')
            ->select('t.*, type_op.nom_operation, op.nom_operateur, u.numero_telephone AS telephone_client')
            ->join('types_operations type_op', 't.type_operation_id = type_op.id')
            ->join('operateurs op',            'type_op.operateur_id = op.id')
            ->join('utilisateurs u',           't.utilisateur_id = u.id')
            ->where('type_op.operateur_id', $operateurId)
            ->orderBy('t.date_creation', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getStatsOperateur(int $operateurId, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $builder = $this->db->table('transactions t')
            ->select('t.montant, t.frais, t.telephone_destinataire, t.montant_a_envoyer, t.commission_externe, type_op.nom_operation')
            ->join('types_operations type_op', 't.type_operation_id = type_op.id')
            ->where('type_op.operateur_id', $operateurId);

        if ($dateDebut) $builder->where('t.date_creation >=', $dateDebut . ' 00:00:00');
        if ($dateFin)   $builder->where('t.date_creation <=', $dateFin   . ' 23:59:59');

        $rows  = $builder->get()->getResultArray();
        
        $operateurModel = new \App\Models\OperateurModel();
        $courantOp = $operateurModel->find($operateurId);

        $stats = [
            'total_transactions' => count($rows),
            'volume_total'       => 0,
            'total_frais'        => 0,
            'gains_retrait'      => 0,
            'gains_transfert_meme_op' => 0,
            'gains_transfert_autre_op' => 0,
            'montants_a_envoyer' => [],
            'par_type'           => [],
        ];

        foreach ($rows as $tx) {
            $stats['volume_total'] += $tx['montant'];
            $stats['total_frais']  += $tx['frais'];
            $t = $tx['nom_operation'];
            
            if ($t === 'retrait') {
                $stats['gains_retrait'] += $tx['frais'];
            }
            if ($t === 'transfert') {
                $destOp = null;
                if (!empty($tx['telephone_destinataire'])) {
                    $destOp = $operateurModel->detecterParTelephone($tx['telephone_destinataire']);
                }

                if ($destOp && $destOp['id'] != $operateurId) {
                    // Pour transfert externe : gains = frais (sans la commission qui va à l'autre op)
                    $stats['gains_transfert_autre_op'] += $tx['frais'];
                    if (!isset($stats['montants_a_envoyer'][$destOp['nom_operateur']])) {
                        $stats['montants_a_envoyer'][$destOp['nom_operateur']] = 0;
                    }
                    // Montant à envoyer = montant reçu par destinataire (inclut frais retrait si option)
                    $montantEnvoyer = !empty($tx['montant_a_envoyer']) ? $tx['montant_a_envoyer'] : $tx['montant'];
                    $stats['montants_a_envoyer'][$destOp['nom_operateur']] += $montantEnvoyer;
                } else {
                    $stats['gains_transfert_meme_op'] += $tx['frais'];
                }
            }
            
            if (!isset($stats['par_type'][$t])) {
                $stats['par_type'][$t] = ['count' => 0, 'volume' => 0, 'frais' => 0];
            }
            $stats['par_type'][$t]['count']++;
            $stats['par_type'][$t]['volume'] += $tx['montant'];
            $stats['par_type'][$t]['frais']  += $tx['frais'];
        }

        $stats['gains_transfert'] = $stats['gains_transfert_meme_op'] + $stats['gains_transfert_autre_op'];
        
        // Calculer les commissions REÇUES (quand d'autres opérateurs envoient vers nous)
        $commissionsRecues = $this->db->table('transactions t')
            ->select('SUM(t.commission_externe) as total')
            ->join('types_operations type_op', 't.type_operation_id = type_op.id')
            ->where('t.telephone_destinataire IS NOT NULL')
            ->where('t.commission_externe >', 0)
            ->get()->getRowArray();
        
        // Vérifier si les destinataires sont de notre opérateur
        $rows2 = $this->db->table('transactions t')
            ->select('t.commission_externe, t.telephone_destinataire')
            ->where('t.commission_externe >', 0)
            ->get()->getResultArray();
            
        $stats['commissions_recues'] = 0;
        foreach ($rows2 as $tx2) {
            if (!empty($tx2['telephone_destinataire'])) {
                $destOp = $operateurModel->detecterParTelephone($tx2['telephone_destinataire']);
                if ($destOp && $destOp['id'] == $operateurId) {
                    $stats['commissions_recues'] += $tx2['commission_externe'];
                }
            }
        }

        return $stats;
    }
}
