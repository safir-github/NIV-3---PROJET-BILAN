<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Class HomeController
 * 
 * Handles public landing page and course showcase.
 * 
 * @package App\Controllers
 */
class HomeController extends BaseController
{
    /**
     * Display landing page with knowledge themes and overview.
     *
     * @return void
     */
    public function index(): void
    {
        $this->render('home/index', [
            'pageTitle' => 'Bienvenue sur Knowledge Learning',
            'message'   => 'Votre plateforme d\'apprentissage et de certification en ligne.'
        ]);
    }
}
