<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperationModel extends Model
{
    protected $table         = 'types_operations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['operateur_id', 'nom_operation'];
    protected $useTimestamps = false;

    public function getByOperateur(int $operateurId): array
    {
        return $this->where('operateur_id', $operateurId)->findAll();
    }

    public function getByOperateurEtType(int $operateurId, string $nomOperation): ?array
    {
        return $this->where('operateur_id', $operateurId)
                    ->where('nom_operation', $nomOperation)
                    ->first();
    }

    public function creerTypesParDefaut(int $operateurId): void
    {
        $baremeFraisModel = new BaremeFraisModel();
        foreach (['depot', 'retrait', 'transfert'] as $nom) {
            $this->insert(['operateur_id' => $operateurId, 'nom_operation' => $nom]);
            $typeId = $this->db->insertID();
            $baremeFraisModel->creerBaremesParDefaut($typeId, $nom);
        }
    }
}
