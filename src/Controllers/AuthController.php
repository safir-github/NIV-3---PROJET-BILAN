<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\PasswordValidator;
use App\Repositories\UserRepository;
use App\Services\Mailer;

/**
 * Class AuthController
 * 
 * Manages user registration, account email activation, login and logout.
 * 
 * @package App\Controllers
 */
class AuthController extends BaseController
{
    private UserRepository $userRepository;
    private PasswordValidator $passwordValidator;
    private Mailer $mailer;

    public function __construct(
        ?UserRepository $userRepository = null,
        ?PasswordValidator $passwordValidator = null,
        ?Mailer $mailer = null
    ) {
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->passwordValidator = $passwordValidator ?? new PasswordValidator();
        $this->mailer = $mailer ?? new Mailer();
    }

    /**
     * Show registration form.
     *
     * @return void
     */
    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->render('auth/register', [
            'pageTitle' => 'Inscription - Knowledge Learning',
            'errors' => [],
            'old' => []
        ]);
    }

    /**
     * Process registration form submission.
     *
     * @return void
     */
    public function register(): void
    {
        $token = $_POST['_csrf_token'] ?? null;
        if (!Csrf::validateToken($token)) {
            $this->render('auth/register', [
                'pageTitle' => 'Inscription - Knowledge Learning',
                'errors' => ['Session de formulaire expirée ou invalide. Veuillez réessayer.'],
                'old' => $_POST
            ]);
            return;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if (empty($firstName) || empty($lastName)) {
            $errors[] = 'Le prénom et le nom sont obligatoires.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email saisie est invalide.';
        } elseif ($this->userRepository->findByEmail($email) !== null) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }

        if ($password !== $passwordConfirmation) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        } else {
            $passwordErrors = $this->passwordValidator->validate($password);
            if (!empty($passwordErrors)) {
                $errors = array_merge($errors, $passwordErrors);
            }
        }

        if (!empty($errors)) {
            $this->render('auth/register', [
                'pageTitle' => 'Inscription - Knowledge Learning',
                'errors' => $errors,
                'old' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email
                ]
            ]);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        if ($hashedPassword === false) {
            // Fallback to bcrypt if Argon2id is not supported
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        }

        $activationToken = bin2hex(random_bytes(32));

        $user = $this->userRepository->create([
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'ROLE_CLIENT',
            'is_active' => 0,
            'activation_token' => $activationToken,
            'created_by' => 'self_registration',
            'updated_by' => 'self_registration'
        ]);

        // Send activation email
        $this->mailer->sendActivationEmail($user->getEmail(), $user->getFirstName(), $activationToken);

        $this->render('auth/activation_sent', [
            'pageTitle' => 'Confirmation d\'inscription - Knowledge Learning',
            'email' => $user->getEmail()
        ]);
    }

    /**
     * Activate account via email token.
     *
     * @return void
     */
    public function activate(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $this->render('auth/login', [
                'pageTitle' => 'Connexion - Knowledge Learning',
                'errors' => ['Jeton d\'activation manquant.'],
                'success' => null
            ]);
            return;
        }

        $user = $this->userRepository->findByActivationToken($token);

        if ($user === null) {
            $this->render('auth/login', [
                'pageTitle' => 'Connexion - Knowledge Learning',
                'errors' => ['Jeton d\'activation invalide ou compte déjà activé.'],
                'success' => null
            ]);
            return;
        }

        $this->userRepository->activateAccount($user->getId());

        $this->render('auth/login', [
            'pageTitle' => 'Connexion - Knowledge Learning',
            'errors' => [],
            'success' => 'Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.'
        ]);
    }

    /**
     * Show login form.
     *
     * @return void
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Connexion - Knowledge Learning',
            'errors' => [],
            'success' => null
        ]);
    }

    /**
     * Process login form submission.
     *
     * @return void
     */
    public function login(): void
    {
        $token = $_POST['_csrf_token'] ?? null;
        if (!Csrf::validateToken($token)) {
            $this->render('auth/login', [
                'pageTitle' => 'Connexion - Knowledge Learning',
                'errors' => ['Session de formulaire expirée. Veuillez réessayer.'],
                'success' => null
            ]);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->userRepository->findByEmail($email);

        // Security: Avoid account enumeration by keeping generic error message
        if ($user === null || !password_verify($password, $user->getPassword())) {
            $this->render('auth/login', [
                'pageTitle' => 'Connexion - Knowledge Learning',
                'errors' => ['Identifiants invalides (email ou mot de passe incorrect).'],
                'success' => null
            ]);
            return;
        }

        // Account must be activated before logging in
        if (!$user->isActive()) {
            $this->render('auth/login', [
                'pageTitle' => 'Connexion - Knowledge Learning',
                'errors' => ['Votre compte n\'a pas encore été activé par email. Veuillez consulter votre boîte de réception.'],
                'success' => null
            ]);
            return;
        }

        // Login user
        Auth::login($user);

        $this->redirect('/');
    }

    /**
     * Process logout request.
     *
     * @return void
     */
    public function logout(): void
    {
        $token = $_POST['_csrf_token'] ?? null;
        if (Csrf::validateToken($token)) {
            Auth::logout();
        }

        $this->redirect('/');
    }
}
