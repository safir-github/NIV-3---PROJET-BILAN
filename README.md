# Knowledge Learning 📖

> Plateforme web d'apprentissage et de certification en ligne développée pour l'entreprise **Knowledge**.
> Projet Bilan de Niveau 3 - Développeur Web (Centre Européen de Formation).

---

## 🛠️ Stack Technique & Architecture

- **Architecture** : MVC 3-tiers découplé en **PHP 8 natif (Orienté Objet)**
- **Base de Données** : MySQL / MariaDB via PDO avec requêtes préparées systématiques
- **Sécurité** :
  - Protection CSRF complète sur tous les formulaires POST via `hash_equals()`
  - Politique de mot de passe fort (8 caractères min, majuscule, minuscule, chiffre, symbole)
  - Hachage sécurisé des mots de passe (`Argon2id` / `Bcrypt`)
  - Activation de compte par lien email sécurisé avec token unique à usage unique
  - Protection anti-énumération de comptes sur la connexion
  - Régénération d'identifiant de session (`session_regenerate_id`) contre la fixation de session
- **Paiement E-Commerce Sandbox** : Stripe Checkout (mode test) validé par Webhook serveur sécurisé par signature cryptographique
- **Tests Unitaires & Sécurité** : PHPUnit 11 (24 tests, 80 assertions, isolation par transactions PDO)
- **Gestionnaire de dépendances** : Composer (Autoloading PSR-4)
- **Environnement** : Configuration sécurisée via Dotenv (`.env`)

---

## 🚀 Installation & Démarrage Rapide

### Prérequis
- PHP >= 8.2 (extensions : `pdo`, `pdo_mysql`, `curl`, `mbstring`)
- Composer
- Serveur de base de données MySQL / MariaDB

### 1. Cloner le projet et installer les dépendances
```bash
git clone <URL_DU_DEPOT>
cd "NIV-3---PROJET-BILAN"
composer install
```

### 2. Configuration d'environnement (.env)
Copiez le modèle de configuration et adaptez vos accès locaux :
```bash
cp .env.example .env
```

---

## 🔒 Sécurité de la Base de Données & Comptes MySQL Dédiés

Conformément aux exigences du barème d'évaluation (*« La base de données n'est accessible que par un compte administrateur spécifiquement créé »*), l'application n'utilise **JAMAIS** le compte `root` en production ni en exécution web.

Deux comptes MySQL distincts sont prévus :

1. **Compte Administrateur BDD (`knowledge_admin`)** :
   - Rôle : Création initiale de la structure, des tables et migrations de schémas (`schema.sql` et `fixtures.sql`).
   - Privilèges : `ALL PRIVILEGES` sur la base `knowledge_learning`.

2. **Compte Applicatif Web Restreint (`knowledge_user`)** :
   - Rôle : Utilisé par l'application PHP (`PDO`) en fonctionnement courant.
   - Privilèges stricts au minimum nécessaire : uniquement `SELECT`, `INSERT`, `UPDATE`, `DELETE`.
   - **Protection** : Les opérations destructrices de structure (`DROP TABLE`, `ALTER TABLE`, `TRUNCATE`) sont formellement interdites et rejetées par le serveur MySQL pour ce compte.

#### Script SQL de création des comptes MySQL :
```sql
-- 1. Création de la base
CREATE DATABASE IF NOT EXISTS knowledge_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Création de l'utilisateur applicatif restreint
CREATE USER IF NOT EXISTS 'knowledge_user'@'localhost' IDENTIFIED BY 'knowledge_secure_pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON knowledge_learning.* TO 'knowledge_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### 3. Initialisation de la base de données
Exécutez dans votre client MySQL (ou phpMyAdmin) :
1. `database/schema.sql` (création des 10 tables et clés étrangères)
2. `database/fixtures.sql` (chargement du catalogue de cours et comptes démo)

### 4. Démarrage du serveur local
```bash
php -S localhost:8000 -t public
```
L'application est immédiatement accessible à l'adresse : **[http://localhost:8000](http://localhost:8000)**.

---

## 👥 Comptes de Démonstration (Fixtures)

| Rôle | Email | Mot de passe | Accès |
|---|---|---|---|
| **Administrateur** | `admin@knowledge.local` | `Password123!` | Accès complet + Backoffice `/admin` |
| **Client (Actif)** | `client@knowledge.local` | `Password123!` | Catalogue, Achat, Progression, Certifications |
| **Client (Inactif)** | `inactive@knowledge.local` | `Password123!` | Compte bloqué avant activation mail |

---

## 🧪 Lancer la suite de tests automatisés (PHPUnit)

```bash
./vendor/bin/phpunit
```
> **Résultat attendu :** 24 tests exécutés avec succès (100% verts), couvrant le routage, la validation de mot de passe, les tokens CSRF, l'authentification, les transactions d'achat, le cycle de certification et la résistance aux injections SQL.

---

## 📊 Modèle Physique de Données (MPD)

La documentation complète du schéma de données relationnel (diagramme entité-relation et explications) est consultable dans :
👉 **[docs/MPD.md](docs/MPD.md)**

---

## 📁 Architecture du Code (MVC 3-tiers)

```text
├── database/            <- Scripts SQL (schéma relationnel et données fixtures)
├── docs/                <- Documentation technique et Modèle Physique de Données (MPD)
├── public/              <- Racine web publique (DocumentRoot)
│   ├── assets/css/      <- Feuille de style officielle Knowledge Learning
│   └── index.php        <- Front Controller unique
├── src/
│   ├── Config/          <- Connexion BDD PDO (Singleton)
│   ├── Controllers/     <- Contrôleurs applicatifs (Auth, Catalog, Checkout, Admin, etc.)
│   ├── Core/            <- Moteur coeur (Router regex, Csrf, Auth, PasswordValidator)
│   ├── Models/          <- Entités métiers représentatives des tables
│   ├── Repositories/    <- Composants d'accès aux données (DAO / Requêtes préparées)
│   └── Services/        <- Services métiers transverses (Stripe, Certification, Mailer)
├── tests/Unit/          <- Tests unitaires automatisés PHPUnit
└── views/               <- Vues et gabarits HTML/PHP
    ├── admin/           <- Vues du backoffice
    ├── auth/            <- Formulaires d'inscription et de connexion
    ├── catalog/         <- Affichage des thèmes, cursus et lecteur de leçon
    ├── certification/   <- Page des attestations de réussite
    ├── checkout/        <- Pages de retour de commande Stripe
    └── layouts/         <- Gabarit principal (header, nav, footer)
```
