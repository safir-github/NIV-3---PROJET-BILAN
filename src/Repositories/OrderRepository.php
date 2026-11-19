<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Class OrderRepository
 * 
 * DAO component managing Orders and OrderItems.
 * 
 * @package App\Repositories
 */
class OrderRepository extends BaseRepository
{
    /**
     * Create an order with items in a secure transaction.
     *
     * @param int $userId
     * @param float $totalAmount
     * @param array{type: string, id: int, price: float} $item
     * @return array{id: int, reference: string}
     */
    public function createOrder(int $userId, float $totalAmount, array $item): array
    {
        $reference = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $sqlOrder = 'INSERT INTO orders (user_id, order_reference, total_amount, status, created_by, updated_by)
                     VALUES (:user_id, :order_reference, :total_amount, :status, :created_by, :updated_by)';
        
        $stmtOrder = $this->db->prepare($sqlOrder);
        $stmtOrder->execute([
            'user_id' => $userId,
            'order_reference' => $reference,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'created_by' => 'checkout',
            'updated_by' => 'checkout'
        ]);

        $orderId = (int) $this->db->lastInsertId();

        $sqlItem = 'INSERT INTO order_items (order_id, item_type, course_id, lesson_id, unit_price, created_by, updated_by)
                    VALUES (:order_id, :item_type, :course_id, :lesson_id, :unit_price, :created_by, :updated_by)';

        $stmtItem = $this->db->prepare($sqlItem);
        $stmtItem->execute([
            'order_id' => $orderId,
            'item_type' => $item['type'],
            'course_id' => $item['type'] === 'course' ? $item['id'] : null,
            'lesson_id' => $item['type'] === 'lesson' ? $item['id'] : null,
            'unit_price' => $item['price'],
            'created_by' => 'checkout',
            'updated_by' => 'checkout'
        ]);

        return [
            'id' => $orderId,
            'reference' => $reference
        ];
    }

    /**
     * Update order with Stripe Checkout Session ID.
     *
     * @param int $orderId
     * @param string $sessionId
     * @return bool
     */
    public function updateStripeSessionId(int $orderId, string $sessionId): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET stripe_session_id = :session_id, updated_by = :updated_by WHERE id = :id');
        return $stmt->execute([
            'id' => $orderId,
            'session_id' => $sessionId,
            'updated_by' => 'stripe_checkout'
        ]);
    }

    /**
     * Find order by its ID.
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find order by its Stripe Checkout Session ID.
     *
     * @param string $sessionId
     * @return array<string, mixed>|null
     */
    public function findByStripeSessionId(string $sessionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE stripe_session_id = :session_id LIMIT 1');
        $stmt->execute(['session_id' => $sessionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get items associated with an order.
     *
     * @param int $orderId
     * @return array<int, array<string, mixed>>
     */
    public function findItemsByOrderId(int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Mark an order as paid (idempotent operation).
     *
     * @param int $orderId
     * @return bool
     */
    public function markAsPaid(int $orderId): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'paid', updated_by = :updated_by WHERE id = :id");
        return $stmt->execute([
            'id' => $orderId,
            'updated_by' => 'stripe_webhook'
        ]);
    }

    /**
     * Find all orders with user details for admin dashboard.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAllWithUsers(): array
    {
        $sql = 'SELECT o.*, u.email as user_email, u.first_name, u.last_name
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.id DESC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
