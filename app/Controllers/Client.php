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
        if ($this->request->is('get')) {
            return view('client/depot');
        }

        // ── Traitement POST ──
        $montant     = (float) $this->request->getPost('montant');
        $userId      = session()->get('user_id');
        $operateurId = session()->get('operateur_id');

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

        $transaction = $this->transactionModel->creerTransaction($userId, $typeOp['id'], $montant, $frais, null, 0, 0);
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
        $userId      = session()->get('user_id');
        $monOperateurId = session()->get('operateur_id');
        
        // Récupérer mon opérateur
        $monOperateur = $this->operateurModel->find($monOperateurId);
        if (!$monOperateur) {
            return redirect()->back()->with('error', 'Opérateur introuvable.');
        }
        
        // Récupérer les barèmes de retrait de mon opérateur
        $typeOp = $this->typeOperationModel->getByOperateurEtType($monOperateurId, 'retrait');
        $baremes = [];
        if ($typeOp) {
            $baremes = $this->baremeFraisModel->getBaremesByTypeOperation($typeOp['id']);
        }

        if ($this->request->is('get')) {
            return view('client/retrait', ['baremes' => $baremes]);
        }

        $montant     = (float) $this->request->getPost('montant');
        $operateurId = $monOperateurId;

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

        $transaction = $this->transactionModel->creerTransaction($userId, $typeOp['id'], $montant, $frais, null, 0, 0);
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
        if ($this->request->is('get')) {
            return view('client/transfert');
        }

        $montantGlobal = (float)  $this->request->getPost('montant');
        $destinataireRaw = trim($this->request->getPost('telephone_destinataire') ?? '');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');
        $userId        = session()->get('user_id');
        $monTelephone  = session()->get('numero_telephone');
        $monOperateurId = session()->get('operateur_id');
        
        // Détection auto de mon opérateur
        $monOperateur = $this->operateurModel->find($monOperateurId);
        if (!$monOperateur) {
            return redirect()->back()->with('error', 'Opérateur introuvable.');
        }
        $operateurId = $monOperateurId;

        if ($montantGlobal < 100) {
            return redirect()->back()->with('error', 'Montant minimum : 100 Ar.');
        }
        if (!$destinataireRaw) {
            return redirect()->back()->with('error', 'Numéro(s) du destinataire requis.');
        }

        $destinatairesStr = preg_replace('/\s+/', '', $destinataireRaw);
        $destinataires = array_filter(explode(',', $destinatairesStr));
        if (empty($destinataires)) {
            return redirect()->back()->with('error', 'Aucun destinataire valide.');
        }

        foreach ($destinataires as $dest) {
            if ($dest === $monTelephone) {
                return redirect()->back()->with('error', 'Vous ne pouvez pas vous transférer à vous-même.');
            }
        }
        
        // Vérifier que pour les envois multiples, tous les destinataires sont du même opérateur
        if (count($destinataires) > 1) {
            $premierDestOp = $this->operateurModel->detecterParTelephone($destinataires[0]);
            foreach ($destinataires as $dest) {
                $destOp = $this->operateurModel->detecterParTelephone($dest);
                if (!$destOp || !$premierDestOp || $destOp['id'] != $premierDestOp['id']) {
                    return redirect()->back()->with('error', 'Pour les envois multiples, tous les destinataires doivent être du même opérateur.');
                }
            }
        }

        $operateur = $this->operateurModel->find($operateurId);
        if (!$operateur) {
            return redirect()->back()->with('error', 'Opérateur invalide.');
        }

        $typeOp = $this->typeOperationModel->getByOperateurEtType($operateurId, 'transfert');
        if (!$typeOp) {
            return redirect()->back()->with('error', 'Type transfert non configuré pour cet opérateur.');
        }

        $expediteur = $this->utilisateurModel->find($userId);
        
        $montantParDestinataire = round($montantGlobal / count($destinataires), 2);

        $totalARetirer = 0;
        $transactionsData = [];

        foreach ($destinataires as $destinataire) {
            $montantAEnvoyer = $montantParDestinataire;
            $destOperateur = $this->operateurModel->detecterParTelephone($destinataire);
            
            // Frais de retrait (seulement pour autres opérateurs)
            $fraisRetraitAjoute = 0;
            if ($inclureFraisRetrait && $destOperateur && $destOperateur['id'] != $operateurId) {
                $typeOpRetrait = $this->typeOperationModel->getByOperateurEtType($destOperateur['id'], 'retrait');
                if ($typeOpRetrait) {
                    $fraisRetrait = $this->baremeFraisModel->calculerFrais($typeOpRetrait['id'], $montantAEnvoyer);
                    $fraisRetraitAjoute = $fraisRetrait;
                }
            }

            // Frais de transfert de mon opérateur
            $fraisTransfert = $this->baremeFraisModel->calculerFrais($typeOp['id'], $montantAEnvoyer);
            
            // Commission de l'opérateur destinataire (si autre opérateur)
            $commissionDest = 0;
            if ($destOperateur && $destOperateur['id'] != $operateurId) {
                $commissionDest = $montantAEnvoyer * (($destOperateur['commission_transfert_externe'] ?? 0) / 100);
            }

            // Total débité de mon compte = montant + frais transfert + commission dest + frais retrait
            $totalDebite = $montantAEnvoyer + $fraisTransfert + $commissionDest + $fraisRetraitAjoute;
            $totalARetirer += $totalDebite;
            
            // Montant que le destinataire recevra
            $montantRecu = $montantAEnvoyer + $fraisRetraitAjoute;

            $transactionsData[] = [
                'destinataire' => $destinataire,
                'montantAEnvoyer' => $montantAEnvoyer,
                'montantRecu' => $montantRecu,
                'fraisTransfert' => $fraisTransfert,
                'commissionDest' => $commissionDest,
                'fraisRetrait' => $fraisRetraitAjoute,
                'totalDebite' => $totalDebite,
                'operateurDest' => $destOperateur
            ];
        }

        if ((float)$expediteur['solde'] < $totalARetirer) {
            return redirect()->back()->with('error', sprintf(
                'Solde insuffisant. Solde : %s Ar — Total requis : %s Ar.',
                number_format($expediteur['solde'], 0, ',', ' '),
                number_format($totalARetirer, 0, ',', ' ')
            ));
        }

        foreach ($transactionsData as $td) {
            $destinataire = $td['destinataire'];
            $montantAEnvoyer = $td['montantAEnvoyer'];
            $montantRecu = $td['montantRecu'];
            $totalDebite = $td['totalDebite'];

            $destinataireUser = $this->utilisateurModel->creerOuGetUtilisateur($destinataire);
            
            // Débiter l'expéditeur
            $soldePrecedent = $this->utilisateurModel->getSolde($userId);
            $this->utilisateurModel->mettreAJourSolde($userId, $totalDebite, 'debit');
            
            // Créditer le destinataire
            $soldePrecedentDest = $this->utilisateurModel->getSolde($destinataireUser['id']);
            $this->utilisateurModel->mettreAJourSolde($destinataireUser['id'], $montantRecu, 'credit');
            
            // Transaction expéditeur (envoi)
            $transaction = $this->transactionModel->creerTransaction(
                $userId, 
                $typeOp['id'], 
                $montantAEnvoyer, 
                $td['fraisTransfert'] + $td['fraisRetrait'],
                $destinataire,
                $montantRecu,
                $td['commissionDest']
            );
            if ($transaction) {
                $nouveauSolde = $this->utilisateurModel->getSolde($userId);
                $this->historiqueSoldeModel->enregistrer($userId, $transaction['id'], $soldePrecedent, $nouveauSolde);
            }
            
            // Transaction destinataire (réception)
            $destOp = $td['operateurDest'];
            if ($destOp) {
                $typeOpDest = $this->typeOperationModel->getByOperateurEtType($destOp['id'], 'transfert');
                if ($typeOpDest) {
                    $transactionDest = $this->transactionModel->creerTransaction(
                        $destinataireUser['id'],
                        $typeOpDest['id'],
                        $montantRecu,
                        0,  // Pas de frais pour celui qui reçoit
                        $monTelephone,  // Téléphone de l'expéditeur
                        0,
                        0
                    );
                    if ($transactionDest) {
                        $nouveauSoldeDest = $this->utilisateurModel->getSolde($destinataireUser['id']);
                        $this->historiqueSoldeModel->enregistrer($destinataireUser['id'], $transactionDest['id'], $soldePrecedentDest, $nouveauSoldeDest);
                    }
                }
            }
        }

        return redirect()->to(base_url('client/dashboard'))
            ->with('success', sprintf(
                'Transfert(s) effectué(s) avec succès. Total débité : %s Ar.',
                number_format($totalARetirer, 0, ',', ' ')
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
