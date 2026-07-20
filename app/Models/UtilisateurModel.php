<?php

namespace App\Models;

use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table         = 'utilisateurs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['numero_telephone', 'solde'];
    protected $useTimestamps = true;
    protected $createdField  = 'date_creation';
    protected $updatedField  = '';
    protected $deletedField  = '';

    public function getByTelephone(string $numero): ?array
    {
        return $this->where('numero_telephone', $numero)->first();
    }

    public function creerOuGetUtilisateur(string $numero): ?array
    {
        $existant = $this->getByTelephone($numero);
        if ($existant) return $existant;

        $this->insert(['numero_telephone' => $numero, 'solde' => 0]);
        return $this->getByTelephone($numero);
    }

    public function mettreAJourSolde(int $id, float $montant, string $type = 'credit'): bool
    {
        $u = $this->find($id);
        if (!$u) return false;

        $nouveauSolde = ($type === 'credit')
            ? $u['solde'] + $montant
            : $u['solde'] - $montant;

        if ($nouveauSolde < 0) return false;
        return (bool) $this->update($id, ['solde' => $nouveauSolde]);
    }

    public function getSolde(int $id): float
    {
        $u = $this->find($id);
        return $u ? (float) $u['solde'] : 0.0;
    }

    public function getUtilisateursByPrefixe(string $prefixes): array
    {
        // Supporte plusieurs préfixes séparés par virgule (ex: "034, 038")
        $prefixeArray = array_map('trim', explode(',', $prefixes));
        
        $builder = $this->builder();
        $builder->groupStart();
        foreach ($prefixeArray as $i => $prefixe) {
            if ($i === 0) {
                $builder->like('numero_telephone', $prefixe, 'after');
            } else {
                $builder->orLike('numero_telephone', $prefixe, 'after');
            }
        }
        $builder->groupEnd();
        
        return $builder->orderBy('date_creation', 'DESC')->get()->getResultArray();
    }
}
