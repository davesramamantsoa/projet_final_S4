<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table         = 'operateurs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom_operateur', 'prefixe_operateur'];
    protected $useTimestamps = false;

    public function getByPrefixe(string $prefixe): ?array
    {
        return $this->where('prefixe_operateur', $prefixe)->first();
    }

    public function prefixeExiste(string $prefixe, ?int $excludeId = null): bool
    {
        $q = $this->where('prefixe_operateur', $prefixe);
        if ($excludeId) $q = $q->where('id !=', $excludeId);
        return $q->first() !== null;
    }

    public function detecterParTelephone(string $numero): ?array
    {
        foreach ($this->findAll() as $op) {
            if (strpos($numero, $op['prefixe_operateur']) === 0) return $op;
        }
        return null;
    }

    public function creerOperateur(string $nom, string $prefixe): ?array
    {
        if ($this->prefixeExiste($prefixe)) return null;
        $this->insert(['nom_operateur' => $nom, 'prefixe_operateur' => $prefixe]);
        return $this->getByPrefixe($prefixe);
    }
}
