<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table         = 'operateurs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom_operateur', 'prefixe_operateur', 'commission_transfert_externe', 'username', 'password'];
    protected $useTimestamps = false;

    public function getByPrefixe(string $prefixe): ?array
    {
        foreach ($this->findAll() as $op) {
            $prefixes = array_map('trim', explode(',', $op['prefixe_operateur']));
            if (in_array(trim($prefixe), $prefixes)) return $op;
        }
        return null;
    }

    public function prefixeExiste(string $prefixeAVerifier, ?int $excludeId = null): bool
    {
        $operateurs = $this->findAll();
        $prefixesToCheck = array_map('trim', explode(',', $prefixeAVerifier));

        foreach ($operateurs as $op) {
            if ($excludeId && $op['id'] == $excludeId) continue;
            
            $prefixesExistants = array_map('trim', explode(',', $op['prefixe_operateur']));
            
            foreach ($prefixesToCheck as $p) {
                if (in_array($p, $prefixesExistants)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function detecterParTelephone(string $numero): ?array
    {
        foreach ($this->findAll() as $op) {
            $prefixes = array_map('trim', explode(',', $op['prefixe_operateur']));
            foreach ($prefixes as $prefixe) {
                if ($prefixe !== '' && strpos($numero, $prefixe) === 0) return $op;
            }
        }
        return null;
    }

    public function creerOperateur(string $nom, string $prefixe): ?array
    {
        if ($this->prefixeExiste($prefixe)) return null;
        $this->insert(['nom_operateur' => $nom, 'prefixe_operateur' => $prefixe]);
        return $this->where('nom_operateur', $nom)->first();
    }
}
