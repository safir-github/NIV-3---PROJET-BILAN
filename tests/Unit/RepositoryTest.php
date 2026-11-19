<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Config\Database;
use App\Repositories\CatalogRepository;
use App\Repositories\UserRepository;
use PDO;

/**
 * Class RepositoryTest
 * 
 * Unit tests validating Data Access Objects (DAO / Repositories) functionality and security.
 * Meets CEF project evaluation requirements (2 points on automated tests).
 * Utilizes database transaction rollbacks to maintain test isolation.
 * 
 * @package App\Tests\Unit
 */
class RepositoryTest extends TestCase
{
    private PDO $db;
    private CatalogRepository $catalogRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection();
        $this->db->beginTransaction();

        $this->catalogRepository = new CatalogRepository($this->db);
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
     * Test retrieving all themes from database.
     */
    public function testFindAllThemesReturnsThemesList(): void
    {
        $themes = $this->catalogRepository->findAllThemes();

        $this->assertIsArray($themes);
        $this->assertNotEmpty($themes);
        $this->assertSame('Musique', $themes[0]->getName());
    }

    /**
     * Test retrieving courses by theme.
     */
    public function testFindCoursesByTheme(): void
    {
        // Theme 1 = Musique (Guitare + Piano)
        $courses = $this->catalogRepository->findCourses(1);

        $this->assertCount(2, $courses);
        $this->assertSame('Cursus Guitare', $courses[0]->getTitle());
        $this->assertSame(50.0, $courses[0]->getPrice());
    }

    /**
     * Test retrieving lessons by course.
     */
    public function testFindLessonsForGuitarCourse(): void
    {
        // Course 1 = Guitare (2 lessons)
        $lessons = $this->catalogRepository->findLessonsByCourseId(1);

        $this->assertCount(2, $lessons);
        $this->assertSame(26.0, $lessons[0]->getPrice());
    }

    /**
     * Test user retrieval by email.
     */
    public function testFindUserByEmail(): void
    {
        $admin = $this->userRepository->findByEmail('admin@knowledge.local');

        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertSame('ROLE_ADMIN', $admin->getRole());
    }

    /**
     * Test user creation with default ROLE_CLIENT and activation.
     */
    public function testCreateUserAndActivationFlow(): void
    {
        $uniqueEmail = 'test_' . uniqid() . '@example.com';
        $user = $this->userRepository->create([
            'email'            => $uniqueEmail,
            'password'         => password_hash('Secret123!', PASSWORD_BCRYPT),
            'first_name'       => 'Test',
            'last_name'        => 'Unit',
            'role'             => 'ROLE_CLIENT',
            'is_active'        => false,
            'activation_token' => 'token_test_abc_123',
            'created_by'       => 'unit_test',
            'updated_by'       => 'unit_test',
        ]);

        $this->assertNotNull($user->getId());
        $this->assertSame('ROLE_CLIENT', $user->getRole());
        $this->assertFalse($user->isActive());

        // Test activation
        $activated = $this->userRepository->activateAccount($user->getId());
        $this->assertTrue($activated);

        $refreshed = $this->userRepository->findById($user->getId());
        $this->assertTrue($refreshed->isActive());
        $this->assertNull($refreshed->getActivationToken());
    }

    /**
     * Test SQL Injection resistance on UserRepository via prepared statements.
     */
    public function testSqlInjectionAttemptFailsSafely(): void
    {
        // SQL injection payload attempting authentication bypass
        $maliciousPayload = "' OR '1'='1";

        $user = $this->userRepository->findByEmail($maliciousPayload);

        // Prepared statement should treat the payload strictly as literal string and return null
        $this->assertNull($user);
    }
}
