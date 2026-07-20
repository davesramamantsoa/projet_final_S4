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
        $validPassword = env('operator.password', 'Admin@1234');

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

    public function creer()
    {
        // CI4 4.4+ : utiliser is() au lieu de getMethod() === 'get'
        if ($this->request->is('get')) {
            return view('operateur/creer');
        }

        $nom     = trim($this->request->getPost('nom_operateur')     ?? '');
        $prefixe = trim($this->request->getPost('prefixe_operateur') ?? '');

        if (!$nom || !$prefixe) {
            return redirect()->back()->with('error', 'Tous les champs sont requis.');
        }
        if ($this->operateurModel->prefixeExiste($prefixe)) {
            return redirect()->back()->with('error', 'Ce préfixe existe déjà.');
        }

        $operateur = $this->operateurModel->creerOperateur($nom, $prefixe);
        if ($operateur) {
            $this->typeOperationModel->creerTypesParDefaut($operateur['id']);
            return redirect()->to(base_url('operateur/dashboard'))
                ->with('success', "Opérateur « $nom » (préfixe $prefixe) créé avec ses barèmes par défaut.");
        }

        return redirect()->back()->with('error', 'Erreur lors de la création.');
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

        return view('operateur/statistiques', [
            'operateur'    => $operateur,
            'stats'        => $this->transactionModel->getStatsOperateur($operateurId, $dateDebut, $dateFin),
            'transactions' => $this->transactionModel->getTransactionsOperateur($operateurId, 50),
            'dateDebut'    => $dateDebut,
            'dateFin'      => $dateFin,
        ]);
    }

    public function clients()
    {
        $operateurs          = $this->operateurModel->findAll();
        $clientsParOperateur = [];

        foreach ($operateurs as $op) {
            $clients = $this->utilisateurModel->getUtilisateursByPrefixe($op['prefixe_operateur']);
            $clientsParOperateur[] = [
                'operateur'    => $op,
                'clients'      => $clients,
                'total_soldes' => array_sum(array_column($clients, 'solde')),
            ];
        }

        return view('operateur/clients', ['clientsParOperateur' => $clientsParOperateur]);
    }
}
