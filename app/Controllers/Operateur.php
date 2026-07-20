<?php

namespace App\Controllers;

use App\Models\OperateurModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\TransactionModel;
use App\Models\UtilisateurModel;

class Operateur extends BaseController
{
    protected $operateurModel;
    protected $typeOperationModel;
    protected $baremeFraisModel;
    protected $transactionModel;
    protected $utilisateurModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->operateurModel     = new OperateurModel();
        $this->typeOperationModel = new TypeOperationModel();
        $this->baremeFraisModel   = new BaremeFraisModel();
        $this->transactionModel   = new TransactionModel();
        $this->utilisateurModel   = new UtilisateurModel();
    }

    public function index()
    {
        if (session()->get('user_type') === 'operator') {
            return redirect()->to(base_url('operateur/dashboard'));
        }
        return view('operateur/login');
    }

    public function login()
    {
        $username = trim($this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';

        $validUsername = env('operator.username', 'admin');
        $validPassword = env('operator.password', 'admin');

        if ($username === $validUsername && $password === $validPassword) {
            session()->set([
                'user_type' => 'operator',
                'username'  => $username,
            ]);
            return redirect()->to(base_url('operateur/dashboard'))->with('success', 'Bienvenue dans l\'espace opérateur.');
        }

        return redirect()->back()->with('error', 'Identifiants invalides.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('operateur'));
    }

    public function dashboard()
    {
        $operateurs = $this->operateurModel->findAll();
        $stats      = [];
        foreach ($operateurs as $op) {
            $stats[$op['id']] = $this->transactionModel->getStatsOperateur($op['id']);
        }
        return view('operateur/dashboard', [
            'operateurs' => $operateurs,
            'stats'      => $stats,
        ]);
    }

    public function config()
    {
        $operateurs = $this->operateurModel->findAll();
        return view('operateur/config', [
            'operateurs' => $operateurs,
        ]);
    }

    public function creer()
    {
        if ($this->request->is('get')) {
            return view('operateur/creer');
        }

        $nom     = trim($this->request->getPost('nom_operateur') ?? '');
        $prefixe = trim($this->request->getPost('prefixe_operateur') ?? '');
        $username = trim($this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';
        $commission = (float)($this->request->getPost('commission_transfert_externe') ?? 0);

        if (!$nom || !$prefixe || !$username || !$password) {
            return redirect()->back()->with('error', 'Tous les champs sont requis.');
        }

        // Vérifier username unique
        if ($this->operateurModel->where('username', $username)->first()) {
            return redirect()->back()->with('error', 'Ce nom d\'utilisateur existe déjà.');
        }

        $id = $this->operateurModel->insert([
            'nom_operateur' => $nom,
            'prefixe_operateur' => $prefixe,
            'commission_transfert_externe' => $commission,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        if ($id) {
            $this->typeOperationModel->creerTypesParDefaut($id);
            return redirect()->to(base_url('operateur'))
                ->with('success', "Opérateur $nom créé ! Login: $username");
        }

        return redirect()->back()->with('error', 'Erreur lors de la création.');
    }

    public function editer(int $id)
    {
        $operateur = $this->operateurModel->find($id);
        if (!$operateur) {
            return redirect()->to(base_url('operateur/dashboard'))->with('error', 'Opérateur introuvable.');
        }

        if ($this->request->is('get')) {
            return view('operateur/editer', ['operateur' => $operateur]);
        }

        $nom = trim($this->request->getPost('nom_operateur') ?? '');
        $prefixe = trim($this->request->getPost('prefixe_operateur') ?? '');
        $commission = (float)($this->request->getPost('commission_transfert_externe') ?? 0);

        if (!$nom || !$prefixe) {
            return redirect()->back()->with('error', 'Le nom et le(s) préfixe(s) sont requis.');
        }
        if ($this->operateurModel->prefixeExiste($prefixe, $id)) {
            return redirect()->back()->with('error', 'Un de ces préfixes existe déjà pour un autre opérateur.');
        }

        $this->operateurModel->update($id, [
            'nom_operateur' => $nom,
            'prefixe_operateur' => $prefixe,
            'commission_transfert_externe' => $commission,
        ]);

        return redirect()->to(base_url('operateur/dashboard'))->with('success', 'Opérateur mis à jour avec succès.');
    }

    public function types(int $operateurId)
    {
        $operateur = $this->operateurModel->find($operateurId);
        if (!$operateur) {
            return redirect()->to(base_url('operateur/dashboard'))->with('error', 'Opérateur introuvable.');
        }

        $types = $this->typeOperationModel->getByOperateur($operateurId);
        foreach ($types as &$type) {
            $type['baremes'] = $this->baremeFraisModel->getBaremesByTypeOperation($type['id']);
        }
        unset($type);

        return view('operateur/types', [
            'operateur' => $operateur,
            'types'     => $types,
        ]);
    }

    public function baremes(int $typeOperationId)
    {
        $typeOp = $this->typeOperationModel->find($typeOperationId);
        if (!$typeOp) {
            return redirect()->to(base_url('operateur/dashboard'))->with('error', 'Type introuvable.');
        }
        $operateur = $this->operateurModel->find($typeOp['operateur_id']);

        if ($this->request->is('get')) {
            return view('operateur/baremes', [
                'typeOp'   => $typeOp,
                'operateur'=> $operateur,
                'baremes'  => $this->baremeFraisModel->getBaremesByTypeOperation($typeOperationId),
            ]);
        }

        // POST — mise à jour des frais
        foreach ($this->request->getPost('baremes') ?? [] as $id => $data) {
            $this->baremeFraisModel->update((int) $id, [
                'montant_frais' => (float) ($data['montant_frais'] ?? 0),
            ]);
        }

        return redirect()->to(base_url('operateur/baremes/' . $typeOperationId))
            ->with('success', 'Barèmes mis à jour avec succès.');
    }

    public function ajouterBareme(int $typeOperationId)
    {
        $typeOp = $this->typeOperationModel->find($typeOperationId);
        if (!$typeOp) {
            return redirect()->to(base_url('operateur/dashboard'))->with('error', 'Type introuvable.');
        }

        $min   = (float) $this->request->getPost('montant_min');
        $max   = (float) $this->request->getPost('montant_max');
        $frais = (float) $this->request->getPost('montant_frais');

        if ($min >= $max || $min < 0 || $frais < 0) {
            return redirect()->back()->with('error', 'Valeurs invalides (min doit être < max et ≥ 0).');
        }

        $this->baremeFraisModel->insert([
            'type_operation_id' => $typeOperationId,
            'montant_min'       => $min,
            'montant_max'       => $max,
            'montant_frais'     => $frais,
        ]);

        return redirect()->to(base_url('operateur/baremes/' . $typeOperationId))
            ->with('success', 'Barème ajouté.');
    }

    public function supprimerBareme(int $baremeId)
    {
        $bareme = $this->baremeFraisModel->find($baremeId);
        if (!$bareme) {
            return redirect()->to(base_url('operateur/dashboard'))->with('error', 'Barème introuvable.');
        }

        $typeOpId = $bareme['type_operation_id'];
        $this->baremeFraisModel->delete($baremeId);

        return redirect()->to(base_url('operateur/baremes/' . $typeOpId))
            ->with('success', 'Barème supprimé.');
    }

    public function statistiques(int $operateurId)
    {
        $operateur = $this->operateurModel->find($operateurId);
        if (!$operateur) {
            return redirect()->to(base_url('operateur/dashboard'))->with('error', 'Opérateur introuvable.');
        }

        $dateDebut = $this->request->getGet('date_debut');
        $dateFin   = $this->request->getGet('date_fin');
        $limit     = (int) ($this->request->getGet('limit') ?? 100);

        return view('operateur/statistiques', [
            'operateur'    => $operateur,
            'stats'        => $this->transactionModel->getStatsOperateur($operateurId, $dateDebut, $dateFin),
            'transactions' => $this->transactionModel->getTransactionsOperateur($operateurId, $limit),
            'dateDebut'    => $dateDebut,
            'dateFin'      => $dateFin,
        ]);
    }

    public function clients()
    {
        // Afficher seulement les clients de MON opérateur (le premier)
        $operateurs = $this->operateurModel->findAll();
        $monOperateur = $operateurs[0] ?? null;
        
        if (!$monOperateur) {
            return redirect()->to(base_url('operateur/dashboard'))
                ->with('error', 'Aucun opérateur configuré.');
        }

        $clients = $this->utilisateurModel->getUtilisateursByPrefixe($monOperateur['prefixe_operateur']);
        $clientsParOperateur = [[
            'operateur'    => $monOperateur,
            'clients'      => $clients,
            'total_soldes' => array_sum(array_column($clients, 'solde')),
        ]];

        return view('operateur/clients', ['clientsParOperateur' => $clientsParOperateur]);
    }
}
