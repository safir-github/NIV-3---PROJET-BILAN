<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\AccessRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\OrderRepository;
use App\Services\StripeService;
use Exception;

/**
 * Class CheckoutController
 * 
 * Handles order creation and redirects user to secure Stripe Checkout Sandbox session.
 * 
 * @package App\Controllers
 */
class CheckoutController extends BaseController
{
    private CatalogRepository $catalogRepository;
    private OrderRepository $orderRepository;
    private AccessRepository $accessRepository;
    private StripeService $stripeService;

    public function __construct(
        ?CatalogRepository $catalogRepository = null,
        ?OrderRepository $orderRepository = null,
        ?AccessRepository $accessRepository = null,
        ?StripeService $stripeService = null
    ) {
        $this->catalogRepository = $catalogRepository ?? new CatalogRepository();
        $this->orderRepository = $orderRepository ?? new OrderRepository();
        $this->accessRepository = $accessRepository ?? new AccessRepository();
        $this->stripeService = $stripeService ?? new StripeService();
    }

    /**
     * Start checkout session for a full course.
     *
     * @param string $id Course ID
     * @return void
     */
    public function checkoutCourse(string $id): void
    {
        $this->processCheckout('course', (int) $id);
    }

    /**
     * Start checkout session for an individual lesson.
     *
     * @param string $id Lesson ID
     * @return void
     */
    public function checkoutLesson(string $id): void
    {
        $this->processCheckout('lesson', (int) $id);
    }

    /**
     * Internal checkout orchestrator ensuring business rules and security.
     *
     * @param string $type 'course' or 'lesson'
     * @param int $itemId
     * @return void
     */
    private function processCheckout(string $type, int $itemId): void
    {
        // 1. Validate CSRF Token
        $token = $_POST['_csrf_token'] ?? null;
        if (!Csrf::validateToken($token)) {
            http_response_code(403);
            $this->render('errors/404', [
                'pageTitle' => 'Erreur de sécurité',
                'message' => 'Jeton de sécurité CSRF invalide ou formulaire expiré. Veuillez réessayer.'
            ]);
            return;
        }

        // 2. User must be authenticated
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        $user = Auth::user();

        // 3. User account must be active (email activated)
        if ($user === null || !$user->isActive()) {
            http_response_code(403);
            $this->render('errors/404', [
                'pageTitle' => 'Achat non autorisé',
                'message' => 'Votre compte doit être activé par email avant de pouvoir commander des cours.'
            ]);
            return;
        }

        // 3. Load item details from DB (Never trust client prices)
        if ($type === 'course') {
            $course = $this->catalogRepository->findCourseById($itemId);
            if ($course === null) {
                $this->redirect('/courses');
                return;
            }

            // Check if already owned
            if ($this->accessRepository->hasCourseAccess($user->getId(), $course->getId())) {
                $this->redirect('/courses/' . $course->getSlug());
                return;
            }

            $title = 'Cursus : ' . $course->getTitle();
            $price = $course->getPrice();
            $itemData = ['type' => 'course', 'id' => $course->getId(), 'price' => $price];
        } else {
            $lesson = $this->catalogRepository->findLessonById($itemId);
            if ($lesson === null) {
                $this->redirect('/courses');
                return;
            }

            // Check if already owned
            if ($this->accessRepository->hasLessonAccess($user->getId(), $lesson->getId(), $lesson->getCourseId())) {
                $this->redirect('/lessons/' . $lesson->getSlug());
                return;
            }

            $title = 'Leçon : ' . $lesson->getTitle();
            $price = $lesson->getPrice();
            $itemData = ['type' => 'lesson', 'id' => $lesson->getId(), 'price' => $price];
        }

        // 4. Create pending Order in database
        $order = $this->orderRepository->createOrder($user->getId(), $price, $itemData);

        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $successUrl = sprintf('%s/checkout/success?session_id={CHECKOUT_SESSION_ID}', rtrim($appUrl, '/'));
        $cancelUrl = sprintf('%s/checkout/cancel?order_id=%d', rtrim($appUrl, '/'), $order['id']);

        try {
            // 5. Create Stripe Checkout Session
            $session = $this->stripeService->createCheckoutSession(
                $order['id'],
                $order['reference'],
                $title,
                $price,
                $successUrl,
                $cancelUrl
            );

            // Save stripe_session_id in database
            $this->orderRepository->updateStripeSessionId($order['id'], $session->id);

            // Redirect to Stripe hosted checkout page
            header('Location: ' . $session->url, true, 303);
            exit;
        } catch (Exception $e) {
            http_response_code(502);
            $this->render('errors/404', [
                'pageTitle' => 'Erreur Passerelle de Paiement',
                'message' => 'Impossible de joindre la passerelle de paiement sécurisée Stripe. Veuillez vérifier votre connexion ou réessayer plus tard.'
            ]);
        }
    }

    /**
     * Display checkout success return page.
     *
     * @return void
     */
    public function success(): void
    {
        $sessionId = $_GET['session_id'] ?? '';
        $order = !empty($sessionId) ? $this->orderRepository->findByStripeSessionId($sessionId) : null;

        $this->render('checkout/success', [
            'pageTitle' => 'Paiement confirmé - Knowledge Learning',
            'order' => $order
        ]);
    }

    /**
     * Display checkout cancellation page.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->render('checkout/cancel', [
            'pageTitle' => 'Paiement annulé - Knowledge Learning'
        ]);
    }
}
