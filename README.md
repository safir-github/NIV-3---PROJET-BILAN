# Knowledge Learning 📖

> Plateforme web d'apprentissage et de certification en ligne développée pour l'entreprise **Knowledge**.
> Projet Bilan de Niveau 3 - Développeur Web (Centre Européen de Formation).

---

## 🛠️ Stack Technique

- **Architecture** : MVC 3-tiers en PHP 8 (Orienté Objet)
- **Base de Données** : MySQL / MariaDB via PDO (requêtes préparées)
- **Paiement Sandbox** : Stripe Checkout (mode test)
- **Gestionnaire de dépendances** : Composer (Autoloading PSR-4)
- **Tests Unitaires** : PHPUnit 11
- **Gestion des variables sensibles** : Dotenv (`.env`)

---

## 🚀 Installation & Démarrage Rapide

### Prérequis
- PHP >= 8.2 (avec extensions `pdo`, `pdo_mysql`, `curl`, `mbstring`)
- Composer
- Serveur MySQL / MariaDB

### 1. Cloner le projet et installer les dépendances
```bash
git clone <URL_DU_DEPOT>
cd "NIV-3---PROJET-BILAN"
composer install
```

### 2. Configuration d'environnement
Copiez le fichier d'exemple et renseignez vos identifiants de base de données :
```bash
cp .env.example .env
```

### 3. Lancer le serveur local de développement
```bash
php -S localhost:8000 -t public
```
L'application est immédiatement accessible à l'adresse : [http://localhost:8000](http://localhost:8000).

---

## 🧪 Lancer la suite de tests unitaires
```bash
./vendor/bin/phpunit
```

---

## 📁 Architecture des Dossiers (MVC 3-tiers)

```text
├── config/              <- Configuration de l'application
├── public/              <- Racine web publique (Front Controller index.php, CSS, images)
│   └── assets/          <- Fichiers statiques (styles, scripts)
├── src/                 <- Code source de l'application (Couche Métier et Données)
│   ├── Config/          <- Connexion BDD PDO Singleton
│   ├── Controllers/     <- Contrôleurs (Logique applicative)
│   ├── Core/            <- Composants coeur (Router, etc.)
│   ├── Models/          <- Entités métiers
│   ├── Repositories/    <- Composants d'accès aux données (DAO)
│   └── Services/        <- Services transverses (Stripe, CSRF, Mailer)
├── tests/               <- Tests unitaires automatisés PHPUnit
├── views/               <- Couche Présentation (Templates HTML/PHP)
│   ├── layouts/         <- Gabarits généraux (en-tête, navigation, pied de page)
│   └── home/            <- Vues associées à la page d'accueil
└── composer.json        <- Dépendances et autoload PSR-4
```
