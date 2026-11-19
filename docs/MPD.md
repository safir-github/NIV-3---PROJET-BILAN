# Modèle Physique de Données (MPD) - Knowledge Learning

Ce document présente la structure relationnelle de la base de données MySQL du projet **Knowledge Learning** (10 tables normalisées avec contraintes d'intégrité référentielle, index d'unicité et traçabilité complète).

---

## 1. Diagramme Entité-Relation (Mermaid)

```mermaid
erDiagram
    users ||--o{ orders : "passe"
    users ||--o{ user_courses : "possede"
    users ||--o{ user_lessons : "possede"
    users ||--o{ lesson_validations : "validates"
    users ||--o{ certifications : "obtient"

    themes ||--|{ courses : "regroupe"
    courses ||--|{ lessons : "contient"

    orders ||--|{ order_items : "comporte"
    courses ||--o{ order_items : "reference"
    lessons ||--o{ order_items : "reference"

    courses ||--o{ user_courses : "accorde"
    lessons ||--o{ user_lessons : "accorde"
    lessons ||--o{ lesson_validations : "enregistre"
    themes ||--o{ certifications : "certifie"
    courses ||--o{ course_validations : "validated_by"
    users ||--o{ course_validations : "validates"

    users {
        int id PK
        string email UK
        string password
        string first_name
        string last_name
        string role
        boolean is_active
        string activation_token
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    themes {
        int id PK
        string name
        string slug UK
        text description
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    courses {
        int id PK
        int theme_id FK
        string title
        string slug UK
        text description
        decimal price
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    lessons {
        int id PK
        int course_id FK
        string title
        string slug UK
        text content_text
        string video_url
        decimal price
        int position
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    orders {
        int id PK
        int user_id FK
        string order_reference UK
        decimal total_amount
        string status
        string stripe_session_id
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    order_items {
        int id PK
        int order_id FK
        string item_type
        int course_id FK
        int lesson_id FK
        decimal unit_price
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    user_courses {
        int id PK
        int user_id FK
        int course_id FK
        datetime purchased_at
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    user_lessons {
        int id PK
        int user_id FK
        int lesson_id FK
        datetime purchased_at
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    lesson_validations {
        int id PK
        int user_id FK
        int lesson_id FK
        datetime validated_at
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    course_validations {
        int id PK
        int user_id FK
        int course_id FK
        datetime validated_at
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }

    certifications {
        int id PK
        int user_id FK
        int theme_id FK
        string certificate_code UK
        datetime issued_at
        datetime created_at
        datetime updated_at
        string created_by
        string updated_by
    }
```

---

## 2. Description des 10 Tables et Justification Métier

1. **`users`** : Stocke les apprenants et administrateurs. Mots de passe hachés (`Argon2id` / `Bcrypt`), rôle par défaut `ROLE_CLIENT`, compte inactif jusqu'à activation par mail (`activation_token`).
2. **`themes`** : Catégories principales de formation (Musique, Informatique, Jardinage, Cuisine).
3. **`courses`** : Cursus complets rattachés à un thème, avec prix et identifiant d'URL unique (`slug`).
4. **`lessons`** : Fiches de cours et vidéos intégrant lecteur sécurisé et prix individuel.
5. **`orders`** : Commandes e-commerce créées avant paiement (`pending`) puis validées par webhook Stripe (`paid`).
6. **`order_items`** : Lignes d'articles rattachées à la commande (cursus ou leçons) avec prix unitaire figé à l'achat.
7. **`user_courses`** : Droits d'accès acquis par l'apprenant pour un cursus complet (contrainte `UNIQUE(user_id, course_id)`).
8. **`user_lessons`** : Droits d'accès acquis par l'apprenant pour une leçon individuelle achetée à l'unité.
9. **`lesson_validations`** : Historique des validations d'étapes d'apprentissage par leçon.
10. **`course_validations`** : Cursus validé quand toutes ses leçons sont validées (contrainte `UNIQUE(user_id, course_id)`).
11. **`certifications`** : Attestation par thème, délivrée quand toutes les leçons du thème sont validées, avec un code d'authenticité unique (`KL-CERT-YYYY-XXXX`).

---

## 3. Conformité aux Exigences du Jury
- **Traçabilité complète** : Toutes les tables comportent systématiquement `created_at`, `updated_at`, `created_by` et `updated_by`.
- **Intégrité référentielle** : Clés étrangères (`FOREIGN KEY`) avec clauses adaptées (`ON DELETE CASCADE` ou `SET NULL`).
- **Index d'unicité** : Prévention native des doublons sur les emails, références de commande et certifications.
