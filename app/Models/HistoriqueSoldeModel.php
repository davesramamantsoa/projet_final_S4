<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoriqueSoldeModel extends Model
{
    protected $table         = 'historique_soldes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['utilisateur_id', 'transaction_id', 'solde_precedent', 'nouveau_solde'];
    protected $useTimestamps = true;
    protected $createdField  = 'date_creation';
    protected $updatedField  = '';
    protected $deletedField  = '';

    public function enregistrer(int $utilisateurId, int $transactionId, float $soldePrecedent, float $nouveauSolde): void
    {
        $this->insert([
            'utilisateur_id'  => $utilisateurId,
            'transaction_id'  => $transactionId,
            'solde_precedent' => $soldePrecedent,
            'nouveau_solde'   => $nouveauSolde,
        ]);
    }
}
