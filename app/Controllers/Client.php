<?php

namespace App\Controllers;

use App\Models\UtilisateurModel;
use App\Models\OperateurModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\TransactionModel;
use App\Models\HistoriqueSoldeModel;

class Client extends BaseController
{
    protected $utilisateurModel;
    protected $operateurModel;
    protected $typeOperationModel;
    protected $baremeFraisModel;
    protected $transactionModel;
    protected $historiqueSoldeModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->utilisateurModel     = new UtilisateurModel();
        $this->operateurModel       = new OperateurModel();
        $this->typeOperationModel   = new TypeOperationModel();
        $this->baremeFraisModel     = new BaremeFraisModel();
        $this->transactionModel     = new TransactionModel();
        $this->historiqueSoldeModel = new HistoriqueSoldeModel();
    }

    // ─────────────────────────────────────────────────
    //  Auth
    // ─────────────────────────────────────────────────

    public function index()
    {
        if (session()->get('user_type') === 'client') {
            return redirect()->to(base_url('client/dashboard'));
        }
        return view('client/login');
    }

    public function login()
    {
        $numero = trim($this->request->getPost('numero_telephone') ?? '');

        if (!$numero) {
            return redirect()->back()->with('error', 'Veuillez entrer votre numéro de téléphone.');
        }

        $numero    = preg_replace('/\s+/', '', $numero);
        $operateur = $this->operateurModel->detecterParTelephone($numero);

        $utilisateur = $this->utilisateurModel->creerOuGetUtilisateur($numero);
        if (!$utilisateur) {
            return redirect()->back()->with('error', 'Erreur lors de la création du compte.');
        }

        session()->set([
            'user_id'           => $utilisateur['id'],
            'numero_telephone'  => $utilisateur['numero_telephone'],
            'user_type'         => 'client',
            'operateur_id'      => $operateur['id']               ?? null,
            'prefixe_operateur' => $operateur['prefixe_operateur'] ?? null,
        ]);

        return redirect()->to(base_url('client/dashboard'))->with('success', 'Bienvenue !');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('client'));
    }

    // ─────────────────────────────────────────────────
    //  Pages
    // ─────────────────────────────────────────────────

    public function dashboard()
    {
        $userId      = session()->get('user_id');
        $utilisateur = $this->utilisateurModel->find($userId);
        $dernieres   = $this->transactionModel->getTransactionsUtilisateur($userId, 5);

        return view('client/dashboard', [
            'utilisateur' => $utilisateur,
            'dernieres'   => $dernieres,
        ]);
    }

    // ─────────────────────────────────────────────────
    //  Dépôt
    // ─────────────────────────────────────────────────

    public function depot()
    {
        $operateurs = $this->operateurModel->findAll();

        // CI4 4.4+ : getMethod() retourne 'GET' en majuscule — utiliser is()
        if ($this->request->is('get')) {
            return view('client/depot', ['operateurs' => $operateurs]);
        }

        // ── Traitement POST ──
        $montant     = (float) $this->request->getPost('montant');
        $operateurId = (int)   $this->request->getPost('operateur_id');
        $userId      = session()->get('user_id');

        if ($montant < 100) {
            return redirect()->back()->with('error', 'Montant minimum : 100 Ar.');
        }

        $operateur = $this->operateurModel->find($operateurId);
        if (!$operateur) {
            return redirect()->back()->with('error', 'Opérateur invalide.');
        }

        $typeOp = $this->typeOperationModel->getByOperateurEtType($operateurId, 'depot');
        if (!$typeOp) {
            return redirect()->back()->with('error', 'Type dépôt non configuré pour cet opérateur.');
        }

        $frais          = $this->baremeFraisModel->calculerFrais($typeOp['id'], $montant); // 0 pour dépôt
        $soldePrecedent = $this->utilisateurModel->getSolde($userId);

        $this->utilisateurModel->mettreAJourSolde($userId, $montant, 'credit');
        $nouveauSolde = $this->utilisateurModel->getSolde($userId);

        $transaction = $this->transactionModel->creerTransaction($userId, $typeOp['id'], $montant, $frais);
        if ($transaction) {
            $this->historiqueSoldeModel->enregistrer($userId, $transaction['id'], $soldePrecedent, $nouveauSolde);
        }

        return redirect()->to(base_url('client/dashboard'))
            ->with('success', 'Dépôt de ' . number_format($montant, 0, ',', ' ') . ' Ar effectué avec succès !');
    }

    // ─────────────────────────────────────────────────
    //  Retrait
    // ─────────────────────────────────────────────────

    public function retrait()
    {
        $operateurs = $this->operateurModel->findAll();

        if ($this->request->is('get')) {
            return view('client/retrait', ['operateurs' => $operateurs]);
        }

        $montant     = (float) $this->request->getPost('montant');
        $operateurId = (int)   $this->request->getPost('operateur_id');
        $userId      = session()->get('user_id');

        if ($montant < 100) {
            return redirect()->back()->with('error', 'Montant minimum : 100 Ar.');
        }

        $operateur = $this->operateurModel->find($operateurId);
        if (!$operateur) {
            return redirect()->back()->with('error', 'Opérateur invalide.');
        }

        $typeOp = $this->typeOperationModel->getByOperateurEtType($operateurId, 'retrait');
        if (!$typeOp) {
            return redirect()->back()->with('error', 'Type retrait non configuré pour cet opérateur.');
        }

        $frais       = $this->baremeFraisModel->calculerFrais($typeOp['id'], $montant);
        $total       = $montant + $frais;
        $utilisateur = $this->utilisateurModel->find($userId);

        if ((float)$utilisateur['solde'] < $total) {
            return redirect()->back()->with('error', sprintf(
                'Solde insuffisant. Solde : %s Ar — Total requis : %s Ar (dont %s Ar de frais).',
                number_format($utilisateur['solde'], 0, ',', ' '),
                number_format($total, 0, ',', ' '),
                number_format($frais, 0, ',', ' ')
            ));
        }

        $soldePrecedent = (float) $utilisateur['solde'];
        $this->utilisateurModel->mettreAJourSolde($userId, $total, 'debit');
        $nouveauSolde = $this->utilisateurModel->getSolde($userId);

        $transaction = $this->transactionModel->creerTransaction($userId, $typeOp['id'], $montant, $frais);
        if ($transaction) {
            $this->historiqueSoldeModel->enregistrer($userId, $transaction['id'], $soldePrecedent, $nouveauSolde);
        }

        return redirect()->to(base_url('client/dashboard'))
            ->with('success', sprintf(
                'Retrait de %s Ar effectué (frais : %s Ar).',
                number_format($montant, 0, ',', ' '),
                number_format($frais, 0, ',', ' ')
            ));
    }

    // ─────────────────────────────────────────────────
    //  Transfert
    // ─────────────────────────────────────────────────

    public function transfert()
    {
        $operateurs = $this->operateurModel->findAll();

        if ($this->request->is('get')) {
            return view('client/transfert', ['operateurs' => $operateurs]);
        }

        $montant      = (float)  $this->request->getPost('montant');
        $operateurId  = (int)    $this->request->getPost('operateur_id');
        $destinataire = trim($this->request->getPost('telephone_destinataire') ?? '');
        $userId       = session()->get('user_id');
        $monTelephone = session()->get('numero_telephone');

        if ($montant < 100) {
            return redirect()->back()->with('error', 'Montant minimum : 100 Ar.');
        }
        if (!$destinataire) {
            return redirect()->back()->with('error', 'Numéro du destinataire requis.');
        }

        $destinataire = preg_replace('/\s+/', '', $destinataire);

        if ($destinataire === $monTelephone) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous transférer à vous-même.');
        }

        $operateur = $this->operateurModel->find($operateurId);
        if (!$operateur) {
            return redirect()->back()->with('error', 'Opérateur invalide.');
        }

        $typeOp = $this->typeOperationModel->getByOperateurEtType($operateurId, 'transfert');
        if (!$typeOp) {
            return redirect()->back()->with('error', 'Type transfert non configuré pour cet opérateur.');
        }

        $frais      = $this->baremeFraisModel->calculerFrais($typeOp['id'], $montant);
        $total      = $montant + $frais;
        $expediteur = $this->utilisateurModel->find($userId);

        if ((float)$expediteur['solde'] < $total) {
            return redirect()->back()->with('error', sprintf(
                'Solde insuffisant. Solde : %s Ar — Total requis : %s Ar.',
                number_format($expediteur['solde'], 0, ',', ' '),
                number_format($total, 0, ',', ' ')
            ));
        }

        $destinataireUser = $this->utilisateurModel->creerOuGetUtilisateur($destinataire);
        if (!$destinataireUser) {
            return redirect()->back()->with('error', 'Erreur avec le compte destinataire.');
        }

        $soldePrecedent = (float) $expediteur['solde'];
        $this->utilisateurModel->mettreAJourSolde($userId, $total, 'debit');
        $this->utilisateurModel->mettreAJourSolde($destinataireUser['id'], $montant, 'credit');

        $transaction = $this->transactionModel->creerTransaction($userId, $typeOp['id'], $montant, $frais, $destinataire);
        if ($transaction) {
            $nouveauSolde = $this->utilisateurModel->getSolde($userId);
            $this->historiqueSoldeModel->enregistrer($userId, $transaction['id'], $soldePrecedent, $nouveauSolde);
        }

        return redirect()->to(base_url('client/dashboard'))
            ->with('success', sprintf(
                'Transfert de %s Ar vers %s effectué (frais : %s Ar).',
                number_format($montant, 0, ',', ' '),
                $destinataire,
                number_format($frais, 0, ',', ' ')
            ));
    }

    // ─────────────────────────────────────────────────
    //  Historique
    // ─────────────────────────────────────────────────

    public function historique()
    {
        $userId     = session()->get('user_id');
        $page       = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit      = 20;
        $offset     = ($page - 1) * $limit;
        $total      = $this->transactionModel->countTransactionsUtilisateur($userId);
        $totalPages = max(1, (int) ceil($total / $limit));

        return view('client/historique', [
            'transactions' => $this->transactionModel->getTransactionsUtilisateur($userId, $limit, $offset),
            'page'         => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
        ]);
    }
}
