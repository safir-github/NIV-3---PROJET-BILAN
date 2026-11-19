# Plan de Soutenance Orale (30 minutes) - Knowledge Learning
Projet Bilan Niveau 3 (CEF) - Candidat : Développeur Web Junior

---

## ⏱️ Répartition du Temps

| Partie | Sujet | Durée |
|---|---|---|
| **1. Introduction** | Présentation personnelle & Contexte de l'entreprise Knowledge | 3 min |
| **2. Architecture & Choix** | Pourquoi PHP 8 MVC 3-tiers natif, Front Controller & PDO | 5 min |
| **3. Base de Données (MPD)** | Modélisation relationnelle (10 tables), traçabilité & DAO | 5 min |
| **4. Sécurité & Authentification** | CSRF, validation mot de passe, tokens d'activation & sessions | 4 min |
| **5. E-Commerce & Certification** | Intégration Stripe Sandbox, webhook serveur & certifications | 5 min |
| **6. Démonstration en direct** | Parcours complet d'un étudiant (inscription -> achat -> certification) | 5 min |
| **7. Conclusion & Questions** | Évolutions futures & Réponses aux questions du jury | 3 min |

---

## 🎯 Points Clés à Mettre en Valeur devant le Jury

1. **Zéro "Boîte Noire"** :
   * Explication de la raison pour laquelle vous avez conçu une architecture MVC native plutôt qu'un gros framework : maîtriser de A à Z le cycle de vie d'une requête HTTP, le routeur et l'accès aux données.
2. **Sécurité Défensive** :
   * Requêtes préparées systématiques empêchant 100% des injections SQL (démontré par notre test unitaire dédié).
   * Protection CSRF avec tokens vérifiés par `hash_equals()`.
   * Webhook Stripe comme unique source de vérité avec vérification de signature.
3. **Qualité & Tests** :
   * 24 tests unitaires PHPUnit (100% verts) avec isolation transactionnelle par rollback.
   * Commentaires de code intégralement en anglais.
