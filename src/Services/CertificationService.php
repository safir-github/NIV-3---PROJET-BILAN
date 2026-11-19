<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificationRepository;
use App\Repositories\ProgressRepository;

/**
 * Class CertificationService
 * 
 * Business logic layer checking course completion and awarding certifications.
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
     * Check course progression and automatically issue a certification if completed.
     *
     * @param int $userId
     * @param int $courseId
     * @return string|null Generated certificate code or null if not yet fully completed
     */
    public function checkAndAwardCourseCertification(int $userId, int $courseId): ?string
    {
        if ($this->progressRepository->isCourseCompleted($userId, $courseId)) {
            return $this->certificationRepository->issueCertification($userId, $courseId);
        }

        return null;
    }
}
