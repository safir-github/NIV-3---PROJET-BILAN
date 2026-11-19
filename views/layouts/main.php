<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Knowledge Learning') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-content">
            <a href="/" class="brand-logo">
                <span class="logo-icon">📖</span>
                <span class="brand-name">KNOWLEDGE <span>Learning</span></span>
            </a>
            <nav class="nav-links">
                <a href="/">Accueil</a>
                <a href="/courses">Catalogue</a>
                <?php if (\App\Core\Auth::check()): ?>
                    <?php $currentUser = \App\Core\Auth::user(); ?>
                    <a href="/certifications">Certifications</a>
                    <span class="user-greeting">Bonjour, <?= htmlspecialchars($currentUser ? $currentUser->getFirstName() : 'Apprenant', ENT_QUOTES, 'UTF-8') ?></span>
                    <form action="/logout" method="POST" style="display:inline;">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-outline btn-sm">Déconnexion</button>
                    </form>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline">Connexion</a>
                    <a href="/register" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        <?= $content ?? '' ?>
    </main>

    <footer class="footer">
        <div class="container footer-content">
            <p>&copy; <?= date('Y') ?> Knowledge Learning - Édition & Formations. Projet Bilan Niveau 3.</p>
        </div>
    </footer>
</body>
</html>
