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
 * Verifies lesson progression, course validation and the theme certification rule
 * of the brief, using isolated database transactions.
 *
 * Fixture data used: theme 1 (Musique) contains course 1 (lessons 1 and 2)
 * and course 2 (lessons 3 and 4).
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
     * Validate a lesson the way the controller does: record it, then run the progression check.
     *
     * @param int $userId
     * @param int $lessonId
     * @param int $courseId
     * @return string|null Certificate code if the theme certification was issued
     */
    private function validateLesson(int $userId, int $lessonId, int $courseId): ?string
    {
        $this->progressRepository->validateLesson($userId, $lessonId);
        return $this->certificationService->checkAndAwardCertification($userId, $courseId);
    }

    /**
     * Create a fresh active client for the test.
     *
     * @return int
     */
    private function createUser(): int
    {
        $user = $this->userRepository->create([
            'email' => 'graduate_' . uniqid() . '@example.com',
            'password' => password_hash('Pass1234!', PASSWORD_BCRYPT),
            'first_name' => 'Emma',
            'last_name' => 'Graduate',
            'role' => 'ROLE_CLIENT',
            'is_active' => 1
        ]);

        return $user->getId();
    }

    /**
     * Completing a course validates it, but the theme certification needs every course of the theme.
     */
    public function testCourseValidationThenThemeCertification(): void
    {
        $userId = $this->createUser();

        // Lessons 1 and 2 complete course 1: course validated, theme not yet complete
        $this->assertNull($this->validateLesson($userId, 1, 1));
        $this->assertNull($this->validateLesson($userId, 2, 1));
        $this->assertTrue($this->progressRepository->hasValidatedCourse($userId, 1));
        $this->assertFalse($this->progressRepository->hasValidatedCourse($userId, 2));
        $this->assertSame([], $this->certificationRepository->findUserCertifications($userId));

        // Lesson 3 alone is not enough
        $this->assertNull($this->validateLesson($userId, 3, 2));
        $this->assertFalse($this->progressRepository->hasValidatedCourse($userId, 2));

        // Lesson 4 completes course 2 and therefore theme 1
        $code = $this->validateLesson($userId, 4, 2);
        $this->assertNotNull($code);
        $this->assertStringStartsWith('KL-CERT-', $code);
        $this->assertTrue($this->progressRepository->hasValidatedCourse($userId, 2));

        // The certificate is attached to the theme, not to a course
        $certs = $this->certificationRepository->findUserCertifications($userId);
        $this->assertCount(1, $certs);
        $this->assertSame('Musique', $certs[0]['theme_name']);
        $this->assertSame($code, $certs[0]['certificate_code']);
    }

    /**
     * Certificates are idempotent: a second check returns the same code and creates no duplicate.
     */
    public function testThemeCertificationIsIdempotent(): void
    {
        $userId = $this->createUser();

        foreach ([1, 2, 3] as $lessonId) {
            $this->validateLesson($userId, $lessonId, $lessonId <= 2 ? 1 : 2);
        }
        $code = $this->validateLesson($userId, 4, 2);

        // Validating the same lesson again must not create another certificate
        $again = $this->certificationService->checkAndAwardCertification($userId, 2);
        $this->assertSame($code, $again);
        $this->assertCount(1, $this->certificationRepository->findUserCertifications($userId));
    }

    /**
     * A lesson validated twice is stored once.
     */
    public function testLessonValidationIsNotDuplicated(): void
    {
        $userId = $this->createUser();

        $this->assertTrue($this->progressRepository->validateLesson($userId, 1));
        $this->assertTrue($this->progressRepository->validateLesson($userId, 1));

        $count = (int) $this->db->query(
            "SELECT COUNT(*) FROM lesson_validations WHERE user_id = {$userId} AND lesson_id = 1"
        )->fetchColumn();
        $this->assertSame(1, $count);
    }
}
