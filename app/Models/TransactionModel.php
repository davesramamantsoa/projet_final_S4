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
        'montant', 'frais', 'telephone_destinataire',
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
        ?string $telephoneDestinataire = null
    ): ?array {
        $data = [
            'reference_transaction'  => $this->genererReference(),
            'utilisateur_id'         => $utilisateurId,
            'type_operation_id'      => $typeOperationId,
            'montant'                => $montant,
            'frais'                  => $frais,
            'telephone_destinataire' => $telephoneDestinataire,
        ];
        if ($this->insert($data)) {
            return $this->where('reference_transaction', $data['reference_transaction'])->first();
        }
        return null;
    }

    public function getTransactionsUtilisateur(int $utilisateurId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->table('transactions t')
            ->select('t.*, type_op.nom_operation, op.nom_operateur, op.prefixe_operateur')
            ->join('types_operations type_op', 't.type_operation_id = type_op.id')
            ->join('operateurs op',            'type_op.operateur_id = op.id')
            ->where('t.utilisateur_id', $utilisateurId)
            ->orderBy('t.date_creation', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResultArray();
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
            ->select('t.montant, t.frais, type_op.nom_operation')
            ->join('types_operations type_op', 't.type_operation_id = type_op.id')
            ->where('type_op.operateur_id', $operateurId);

        if ($dateDebut) $builder->where('t.date_creation >=', $dateDebut . ' 00:00:00');
        if ($dateFin)   $builder->where('t.date_creation <=', $dateFin   . ' 23:59:59');

        $rows  = $builder->get()->getResultArray();
        $stats = [
            'total_transactions' => count($rows),
            'volume_total'       => 0,
            'total_frais'        => 0,
            'gains_retrait'      => 0,
            'gains_transfert'    => 0,
            'par_type'           => [],
        ];

        foreach ($rows as $tx) {
            $stats['volume_total'] += $tx['montant'];
            $stats['total_frais']  += $tx['frais'];
            $t = $tx['nom_operation'];
            if ($t === 'retrait')   $stats['gains_retrait']   += $tx['frais'];
            if ($t === 'transfert') $stats['gains_transfert'] += $tx['frais'];
            if (!isset($stats['par_type'][$t])) {
                $stats['par_type'][$t] = ['count' => 0, 'volume' => 0, 'frais' => 0];
            }
            $stats['par_type'][$t]['count']++;
            $stats['par_type'][$t]['volume'] += $tx['montant'];
            $stats['par_type'][$t]['frais']  += $tx['frais'];
        }

        return $stats;
    }
}
