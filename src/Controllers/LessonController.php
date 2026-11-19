<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\AccessRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\ProgressRepository;
use App\Services\CertificationService;

/**
 * Class LessonController
 * 
 * Handles lesson progress validation and triggers automated certification check.
 * 
 * @package App\Controllers
 */
class LessonController extends BaseController
{
    private CatalogRepository $catalogRepository;
    private AccessRepository $accessRepository;
    private ProgressRepository $progressRepository;
    private CertificationService $certificationService;

    public function __construct(
        ?CatalogRepository $catalogRepository = null,
        ?AccessRepository $accessRepository = null,
        ?ProgressRepository $progressRepository = null,
        ?CertificationService $certificationService = null
    ) {
        $this->catalogRepository = $catalogRepository ?? new CatalogRepository();
        $this->accessRepository = $accessRepository ?? new AccessRepository();
        $this->progressRepository = $progressRepository ?? new ProgressRepository();
        $this->certificationService = $certificationService ?? new CertificationService();
    }

    /**
     * Validate a lesson for the currently authenticated learner.
     *
     * @param string $id Lesson ID
     * @return void
     */
    public function validate(string $id): void
    {
        // 1. Must be authenticated
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        // 2. Validate CSRF token
        $token = $_POST['_csrf_token'] ?? null;
        if (!Csrf::validateToken($token)) {
            $this->redirect('/courses');
            return;
        }

        $user = Auth::user();
        $lessonId = (int) $id;
        $lesson = $this->catalogRepository->findLessonById($lessonId);

        if ($lesson === null || $user === null) {
            $this->redirect('/courses');
            return;
        }

        // 3. User must have acquired access to this lesson
        $hasAccess = $this->accessRepository->hasLessonAccess($user->getId(), $lesson->getId(), $lesson->getCourseId());
        if (!$hasAccess) {
            $this->redirect('/lessons/' . $lesson->getSlug());
            return;
        }

        // 4. Save validation
        $this->progressRepository->validateLesson($user->getId(), $lesson->getId());

        // 5. Update course validation and theme certification when the rules are met
        $this->certificationService->checkAndAwardCertification($user->getId(), $lesson->getCourseId());

        // Redirect back to lesson page
        $this->redirect('/lessons/' . $lesson->getSlug());
    }
}
