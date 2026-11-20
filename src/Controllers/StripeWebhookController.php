<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AccessRepository;
use App\Repositories\OrderRepository;
use App\Services\StripeService;
use Exception;

/**
 * Class StripeWebhookController
 * 
 * Handles incoming asynchronous Stripe webhooks to fulfill orders safely.
 * Source of truth for payment confirmations (cannot be spoofed from client).
 * 
 * @package App\Controllers
 */
class StripeWebhookController
{
    private OrderRepository $orderRepository;
    private AccessRepository $accessRepository;
    private StripeService $stripeService;

    /**
     * Create a new object.
     */
    public function __construct(
        ?OrderRepository $orderRepository = null,
        ?AccessRepository $accessRepository = null,
        ?StripeService $stripeService = null
    ) {
        $this->orderRepository = $orderRepository ?? new OrderRepository();
        $this->accessRepository = $accessRepository ?? new AccessRepository();
        $this->stripeService = $stripeService ?? new StripeService();
    }

    /**
     * Handle incoming webhook requests.
     *
     * @return void
     */
    public function handle(): void
    {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (empty($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Empty webhook payload']);
            return;
        }

        try {
            // Cryptographically verify Stripe signature
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid webhook signature: ' . $e->getMessage()]);
            return;
        }

        // Process successful payment
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = isset($session->client_reference_id) ? (int) $session->client_reference_id : null;
            $order = $orderId ? $this->orderRepository->findById($orderId) : null;

            if ($order !== null && $this->isPaymentConfirmed($session, $order)) {
                $this->fulfillOrder($orderId);
            } elseif ($order !== null) {
                // Never grant access for an unpaid or mismatched session
                error_log('Stripe webhook ignored for order ' . $orderId . ': payment not confirmed or amount mismatch');
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'success']);
    }

    /**
     * Check that a Checkout Session was really paid, for the amount of the order.
     *
     * Stripe amounts are in cents, so the order total (e.g. 50.00) is compared as 5000.
     *
     * @param object $session The checkout.session object sent by Stripe
     * @param array  $order   The local order row
     * @return bool
     */
    public function isPaymentConfirmed(object $session, array $order): bool
    {
        $paid = ($session->payment_status ?? null) === 'paid';
        $expectedCents = (int) round(((float) $order['total_amount']) * 100);
        $amountMatches = (int) ($session->amount_total ?? -1) === $expectedCents;

        return $paid && $amountMatches;
    }

    /**
     * Fulfill order in an idempotent manner.
     *
     * @param int $orderId
     * @return void
     */
    public function fulfillOrder(int $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            return;
        }

        // Idempotency: Avoid processing already paid orders twice
        if ($order['status'] === 'paid') {
            return;
        }

        // 1. Mark order as paid
        $this->orderRepository->markAsPaid($orderId);

        // 2. Grant access to purchased items
        $items = $this->orderRepository->findItemsByOrderId($orderId);
        $userId = (int) $order['user_id'];

        foreach ($items as $item) {
            if ($item['item_type'] === 'course' && !empty($item['course_id'])) {
                $this->accessRepository->grantCourseAccess($userId, (int) $item['course_id']);
            } elseif ($item['item_type'] === 'lesson' && !empty($item['lesson_id'])) {
                $this->accessRepository->grantLessonAccess($userId, (int) $item['lesson_id']);
            }
        }
    }
}
