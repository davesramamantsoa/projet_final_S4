<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController
 *
 * Classe de base pour tous les contrôleurs de l'application.
 * Étendre cette classe dans chaque nouveau contrôleur.
 */
abstract class BaseController extends Controller
{
    /**
     * Helpers chargés automatiquement pour tous les contrôleurs enfants.
     */
    protected $helpers = ['url', 'form', 'text'];

    /**
     * Initialisation du contrôleur.
     *
     * @return void
     */
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
    }
}
