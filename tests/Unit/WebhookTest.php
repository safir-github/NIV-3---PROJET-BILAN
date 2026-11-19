<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\StripeWebhookController;
use PHPUnit\Framework\TestCase;

/**
 * Class WebhookTest
 *
 * Verifies the rule that protects order fulfilment: a Stripe session must be paid,
 * and must carry the same amount (in cents) as the local order.
 * Pure logic test: no query and no network call (the controller only opens its DB connection).
 *
 * @package Tests\Unit
 */
class WebhookTest extends TestCase
{
    /**
     * Build a fake checkout.session object.
     *
     * @param string $paymentStatus
     * @param int    $amountTotal Amount in cents
     * @return object
     */
    private function session(string $paymentStatus, int $amountTotal): object
    {
        return (object) [
            'id' => 'cs_test_' . uniqid(),
            'object' => 'checkout.session',
            'payment_status' => $paymentStatus,
            'amount_total' => $amountTotal,
        ];
    }

    /**
     * A paid session with the order amount is accepted.
     */
    public function testPaidSessionWithMatchingAmountIsConfirmed(): void
    {
        $controller = new StripeWebhookController();
        $order = ['total_amount' => '50.00'];

        $this->assertTrue($controller->isPaymentConfirmed($this->session('paid', 5000), $order));
    }

    /**
     * An unpaid session is refused, even if the amount matches.
     */
    public function testUnpaidSessionIsRefused(): void
    {
        $controller = new StripeWebhookController();
        $order = ['total_amount' => '50.00'];

        $this->assertFalse($controller->isPaymentConfirmed($this->session('unpaid', 5000), $order));
    }

    /**
     * A session paid for a different amount is refused.
     */
    public function testAmountMismatchIsRefused(): void
    {
        $controller = new StripeWebhookController();
        $order = ['total_amount' => '50.00'];

        $this->assertFalse($controller->isPaymentConfirmed($this->session('paid', 2600), $order));
    }

    /**
     * A session without the expected fields is refused instead of raising a warning.
     */
    public function testMissingFieldsAreRefused(): void
    {
        $controller = new StripeWebhookController();
        $order = ['total_amount' => '50.00'];

        $this->assertFalse($controller->isPaymentConfirmed((object) [], $order));
    }
}
