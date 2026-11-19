<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config\Database;
use App\Repositories\CertificationRepository;
use App\Repositories\ProgressRepository;
use App\Repositories\UserRepository;
use App\Services\CertificationService;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Class CertificationTest
 * 
 * Verifies lesson progression tracking, curriculum completion checks,
 * and automated issuance of course certifications with isolated database transactions.
 * 
 * @package Tests\Unit
 */
class CertificationTest extends TestCase
{
    private PDO $db;
    private ProgressRepository $progressRepository;
    private CertificationRepository $certificationRepository;
    private CertificationService $certificationService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection();
        $this->db->beginTransaction();

        $this->progressRepository = new ProgressRepository($this->db);
        $this->certificationRepository = new CertificationRepository($this->db);
        $this->certificationService = new CertificationService($this->progressRepository, $this->certificationRepository);
        $this->userRepository = new UserRepository($this->db);
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        parent::tearDown();
    }

    /**
     * Test full certification flow: validating all lessons of a course unlocks certification.
     */
    public function testCourseCompletionAndCertificationIssuance(): void
    {
        $user = $this->userRepository->create([
            'email' => 'graduate_' . uniqid() . '@example.com',
            'password' => password_hash('Pass1234!', PASSWORD_BCRYPT),
            'first_name' => 'Emma',
            'last_name' => 'Graduate',
            'role' => 'ROLE_CLIENT',
            'is_active' => 1
        ]);

        $userId = $user->getId();
        $courseId = 1; // Course 1 (Guitare débutant: 2 lessons in fixtures: lesson 1 and 2)

        // Initially course is not completed
        $this->assertFalse($this->progressRepository->isCourseCompleted($userId, $courseId));
        $this->assertNull($this->certificationService->checkAndAwardCourseCertification($userId, $courseId));

        // Validate Lesson 1
        $this->assertTrue($this->progressRepository->validateLesson($userId, 1));
        $this->assertFalse($this->progressRepository->isCourseCompleted($userId, $courseId));

        // Validate Lesson 2 (Now all lessons in Course 1 are validated)
        $this->assertTrue($this->progressRepository->validateLesson($userId, 2));
        $this->assertTrue($this->progressRepository->isCourseCompleted($userId, $courseId));

        // Automatically issue certification
        $certCode = $this->certificationService->checkAndAwardCourseCertification($userId, $courseId);
        $this->assertNotNull($certCode);
        $this->assertStringStartsWith('KL-CERT-', $certCode);

        // Verify certification is retrievable
        $userCerts = $this->certificationRepository->findUserCertifications($userId);
        $this->assertCount(1, $userCerts);
        $this->assertSame($certCode, $userCerts[0]['certificate_code']);

        // Idempotency: calling certification service again returns the same certificate code without error
        $reIssuedCode = $this->certificationService->checkAndAwardCourseCertification($userId, $courseId);
        $this->assertSame($certCode, $reIssuedCode);
    }
}
