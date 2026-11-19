<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config\Database;
use App\Models\User;
use App\Repositories\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Class AuthTest
 * 
 * Verifies User creation, client role assignment, activation token lifecycle,
 * and secure account activation with isolated database transactions.
 * 
 * @package Tests\Unit
 */
class AuthTest extends TestCase
{
    private PDO $db;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection();
        $this->db->beginTransaction();
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
     * Test registration assigns default ROLE_CLIENT and inactive status.
     */
    public function testRegistrationAssignsClientRoleAndInactiveStatus(): void
    {
        $activationToken = bin2hex(random_bytes(32));
        $testEmail = 'new_student_' . uniqid() . '@example.com';

        $user = $this->userRepository->create([
            'email' => $testEmail,
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'first_name' => 'Alice',
            'last_name' => 'Dubois',
            'role' => 'ROLE_CLIENT',
            'is_active' => 0,
            'activation_token' => $activationToken,
            'created_by' => 'unit_test',
            'updated_by' => 'unit_test'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('ROLE_CLIENT', $user->getRole());
        $this->assertFalse($user->isActive());
        $this->assertSame($activationToken, $user->getActivationToken());
    }

    /**
     * Test finding user by activation token and successful account activation.
     */
    public function testAccountActivationLifecycle(): void
    {
        $activationToken = bin2hex(random_bytes(32));
        $testEmail = 'activate_test_' . uniqid() . '@example.com';

        $user = $this->userRepository->create([
            'email' => $testEmail,
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'first_name' => 'Bob',
            'last_name' => 'Martin',
            'role' => 'ROLE_CLIENT',
            'is_active' => 0,
            'activation_token' => $activationToken,
            'created_by' => 'unit_test',
            'updated_by' => 'unit_test'
        ]);

        // Find by token
        $foundUser = $this->userRepository->findByActivationToken($activationToken);
        $this->assertNotNull($foundUser);
        $this->assertSame($user->getId(), $foundUser->getId());

        // Activate account
        $activated = $this->userRepository->activateAccount($foundUser->getId());
        $this->assertTrue($activated);

        // Verify state after activation
        $updatedUser = $this->userRepository->findById($foundUser->getId());
        $this->assertNotNull($updatedUser);
        $this->assertTrue($updatedUser->isActive());
        $this->assertNull($updatedUser->getActivationToken());

        // Token should now be invalid/consumed
        $consumedLookup = $this->userRepository->findByActivationToken($activationToken);
        $this->assertNull($consumedLookup);
    }
}
