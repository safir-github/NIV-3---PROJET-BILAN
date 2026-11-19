<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificationRepository;
use App\Repositories\ProgressRepository;

/**
 * Class CertificationService
 *
 * Business logic layer for the progression rules of the brief:
 * - when every lesson of a course is validated, the course is validated;
 * - when every lesson of a theme is validated, the theme certification is issued.
 * Decouples business rules from controllers and repositories.
 *
 * @package App\Services
 */
class CertificationService
{
    private ProgressRepository $progressRepository;
    private CertificationRepository $certificationRepository;

    public function __construct(
        ?ProgressRepository $progressRepository = null,
        ?CertificationRepository $certificationRepository = null
    ) {
        $this->progressRepository = $progressRepository ?? new ProgressRepository();
        $this->certificationRepository = $certificationRepository ?? new CertificationRepository();
    }

    /**
     * Update course and theme progression after a lesson of $courseId was validated.
     *
     * @param int $userId
     * @param int $courseId The course of the lesson that was just validated
     * @return string|null Certificate code if the theme certification was issued by this call (or already exists), null otherwise
     */
    public function checkAndAwardCertification(int $userId, int $courseId): ?string
    {
        // 1. Course validation: every lesson of the course is validated
        if (!$this->progressRepository->isCourseCompleted($userId, $courseId)) {
            return null;
        }
        $this->progressRepository->recordCourseValidation($userId, $courseId);

        // 2. Theme certification: every lesson of the theme is validated
        $themeId = $this->progressRepository->findThemeIdForCourse($courseId);
        if ($themeId === null || !$this->progressRepository->isThemeCompleted($userId, $themeId)) {
            return null;
        }

        return $this->certificationRepository->issueCertification($userId, $themeId);
    }
}
