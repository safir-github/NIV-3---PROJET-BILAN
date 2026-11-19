<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config\Database;
use App\Repositories\AccessRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Class PurchaseTest
 * 
 * Verifies purchase lifecycle, order creation, access granting, and idempotency.
 * Uses isolated database transactions.
 * 
 * @package Tests\Unit
 */
class PurchaseTest extends TestCase
{
    private PDO $db;
    private OrderRepository $orderRepository;
    private AccessRepository $accessRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection();
        $this->db->beginTransaction();

        $this->orderRepository = new OrderRepository($this->db);
        $this->accessRepository = new AccessRepository($this->db);
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
     * Test order creation and items persistence with server-side prices.
     */
    public function testOrderCreation(): void
    {
        $user = $this->userRepository->create([
            'email' => 'buyer_' . uniqid() . '@example.com',
            'password' => password_hash('Pass1234!', PASSWORD_BCRYPT),
            'first_name' => 'Charlie',
            'last_name' => 'Buyer',
            'role' => 'ROLE_CLIENT',
            'is_active' => 1
        ]);

        $orderData = $this->orderRepository->createOrder(
            $user->getId(),
            50.00,
            ['type' => 'course', 'id' => 1, 'price' => 50.00]
        );

        $this->assertNotEmpty($orderData['id']);
        $this->assertStringStartsWith('ORD-', $orderData['reference']);

        $order = $this->orderRepository->findById($orderData['id']);
        $this->assertNotNull($order);
        $this->assertSame('pending', $order['status']);
        $this->assertEquals(50.00, (float)$order['total_amount']);

        $items = $this->orderRepository->findItemsByOrderId($orderData['id']);
        $this->assertCount(1, $items);
        $this->assertSame('course', $items[0]['item_type']);
        $this->assertSame(1, (int)$items[0]['course_id']);
    }

    /**
     * Test granting access upon payment and idempotency.
     */
    public function testGrantAccessAndIdempotency(): void
    {
        $user = $this->userRepository->create([
            'email' => 'access_' . uniqid() . '@example.com',
            'password' => password_hash('Pass1234!', PASSWORD_BCRYPT),
            'first_name' => 'David',
            'last_name' => 'Student',
            'role' => 'ROLE_CLIENT',
            'is_active' => 1
        ]);

        $userId = $user->getId();
        $courseId = 1;

        // User initially has no access
        $this->assertFalse($this->accessRepository->hasCourseAccess($userId, $courseId));

        // Grant access
        $granted = $this->accessRepository->grantCourseAccess($userId, $courseId);
        $this->assertTrue($granted);
        $this->assertTrue($this->accessRepository->hasCourseAccess($userId, $courseId));

        // Idempotency: Granting second time must not throw an error (due to ON DUPLICATE KEY UPDATE)
        $reGranted = $this->accessRepository->grantCourseAccess($userId, $courseId);
        $this->assertTrue($reGranted);
        $this->assertTrue($this->accessRepository->hasCourseAccess($userId, $courseId));
    }
}
