<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\CertificationRepository;

/**
 * Class CertificationController
 * 
 * Displays student achievements and awarded certifications.
 * 
 * @package App\Controllers
 */
class CertificationController extends BaseController
{
    private CertificationRepository $certificationRepository;

    /**
     * Create a new object.
     */
    public function __construct(?CertificationRepository $certificationRepository = null)
    {
        $this->certificationRepository = $certificationRepository ?? new CertificationRepository();
    }

    /**
     * Display current user's certifications.
     *
     * @return void
     */
    public function index(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        $user = Auth::user();
        $certifications = $user ? $this->certificationRepository->findUserCertifications($user->getId()) : [];

        $this->render('certification/index', [
            'pageTitle' => 'Mes Certifications - Knowledge Learning',
            'certifications' => $certifications
        ]);
    }
}
