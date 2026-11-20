<?php

declare(strict_types=1);

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Event;
use Exception;

/**
 * Class StripeService
 * 
 * Encapsulates official Stripe PHP SDK interactions for Checkout Sessions and Webhooks.
 * 
 * @package App\Services
 */
class StripeService
{
    private string $secretKey;
    private string $webhookSecret;

    /**
     * Create a new object.
     */
    public function __construct(?string $secretKey = null, ?string $webhookSecret = null)
    {
        $this->secretKey = $secretKey ?? ($_ENV['STRIPE_SECRET_KEY'] ?? '');
        $this->webhookSecret = $webhookSecret ?? ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');

        if (!empty($this->secretKey)) {
            Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * Create a Stripe Checkout Session for one course or lesson.
     *
     * @param int $orderId
     * @param string $orderReference
     * @param string $itemName
     * @param float $priceAmount
     * @param string $successUrl
     * @param string $cancelUrl
     * @return Session
     * @throws Exception
     */
    public function createCheckoutSession(
        int $orderId,
        string $orderReference,
        string $itemName,
        float $priceAmount,
        string $successUrl,
        string $cancelUrl
    ): Session {
        // Stripe expects unit amount in cents (e.g. 50.00 EUR = 5000 cents)
        $unitAmount = (int) round($priceAmount * 100);

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $itemName,
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => (string) $orderId,
            'metadata' => [
                'order_reference' => $orderReference,
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    /**
     * Construct and cryptographically verify incoming Stripe webhook event.
     *
     * @param string $payload Raw HTTP body
     * @param string $sigHeader Stripe-Signature HTTP header
     * @return Event
     * @throws Exception
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }
}
