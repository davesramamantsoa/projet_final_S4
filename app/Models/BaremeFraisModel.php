<?php

namespace App\Models;

use CodeIgniter\Model;

class BaremeFraisModel extends Model
{
    protected $table         = 'baremes_frais';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['type_operation_id', 'montant_min', 'montant_max', 'montant_frais'];
    protected $useTimestamps = false;

    public function getBaremesByTypeOperation(int $typeOperationId): array
    {
        return $this->where('type_operation_id', $typeOperationId)
                    ->orderBy('montant_min', 'ASC')
                    ->findAll();
    }

    public function calculerFrais(int $typeOperationId, float $montant): float
    {
        $b = $this->where('type_operation_id', $typeOperationId)
                  ->where('montant_min <=', $montant)
                  ->where('montant_max >=', $montant)
                  ->first();
        return $b ? (float) $b['montant_frais'] : 0.0;
    }

    public function creerBaremesParDefaut(int $typeOperationId, string $nomOperation): void
    {
        $mult = ($nomOperation === 'depot') ? 0 : 1;
        $baremes = [
            [100,     1000,    50   * $mult],
            [1001,    5000,    50   * $mult],
            [5001,    10000,   100  * $mult],
            [10001,   25000,   200  * $mult],
            [25001,   50000,   400  * $mult],
            [50001,   100000,  800  * $mult],
            [100001,  250000,  1500 * $mult],
            [250001,  500000,  1500 * $mult],
            [500001,  1000000, 2500 * $mult],
            [1000001, 2000000, 3000 * $mult],
        ];
        foreach ($baremes as [$min, $max, $frais]) {
            $this->insert([
                'type_operation_id' => $typeOperationId,
                'montant_min'       => $min,
                'montant_max'       => $max,
                'montant_frais'     => $frais,
            ]);
        }
    }
}
