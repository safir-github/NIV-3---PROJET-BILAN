-- =====================================================================
-- Knowledge Learning - Fixtures & Test Data (Données Bêta du brief)
-- =====================================================================

-- 1. Utilisateurs de test initiaux
-- Mot de passe haché avec password_hash('Password123!', PASSWORD_BCRYPT) : $2y$12$NqLqf1vS9t3R5yB0bC1xOu0uFzR9q4bN8yD1x9u0v1w2x3y4z5a6b (ou généré dynamiquement)
-- Pour tests rapides : Mot de passe = Password123!
INSERT INTO users (id, email, password, first_name, last_name, role, is_active, activation_token, created_by, updated_by) VALUES
(1, 'admin@knowledge.local', '$2y$10$wT5fN8MUp7hS7yP7yH3FpuoI5k1sV0I1FwY2D2k0u3V4x5y6z7a8b', 'Admin', 'Knowledge', 'ROLE_ADMIN', 1, NULL, 'seed', 'seed'),
(2, 'client@knowledge.local', '$2y$10$wT5fN8MUp7hS7yP7yH3FpuoI5k1sV0I1FwY2D2k0u3V4x5y6z7a8b', 'Jean', 'Dupont', 'ROLE_CLIENT', 1, NULL, 'seed', 'seed'),
(3, 'inactif@knowledge.local', '$2y$10$wT5fN8MUp7hS7yP7yH3FpuoI5k1sV0I1FwY2D2k0u3V4x5y6z7a8b', 'Pierre', 'Martin', 'ROLE_CLIENT', 0, 'dummy_token_123456', 'seed', 'seed');

-- 2. Thèmes officiels
INSERT INTO themes (id, name, slug, description, created_by, updated_by) VALUES
(1, 'Musique', 'musique', 'Découvrez et perfectionnez votre pratique instrumentale.', 'seed', 'seed'),
(2, 'Informatique', 'informatique', 'Maîtrisez les technologies du développement web moderne.', 'seed', 'seed'),
(3, 'Jardinage', 'jardinage', 'Apprenez à cultiver votre potager et entretenir vos espaces verts.', 'seed', 'seed'),
(4, 'Cuisine', 'cuisine', 'Explorez les techniques culinaires et l''art du dressage.', 'seed', 'seed');

-- 3. Cursus officiels (Prix conformes au brief)
INSERT INTO courses (id, theme_id, title, slug, description, price, created_by, updated_by) VALUES
(1, 1, 'Cursus Guitare', 'cursus-guitare', 'Apprenez la guitare acoustique et électrique de débutant à avancé.', 50.00, 'seed', 'seed'),
(2, 1, 'Cursus Piano', 'cursus-piano', 'Initiez-vous au piano classique et moderne.', 50.00, 'seed', 'seed'),
(3, 2, 'Cursus Dev Web', 'cursus-dev-web', 'Devenez développeur web full-stack de zéro.', 60.00, 'seed', 'seed'),
(4, 3, 'Cursus Jardinage', 'cursus-jardinage', 'Les secrets d''un jardin florissant en toute saison.', 30.00, 'seed', 'seed'),
(5, 4, 'Cursus Cuisine', 'cursus-cuisine', 'Les bases indispensables de la grande gastronomie.', 44.00, 'seed', 'seed'),
(6, 4, 'Cursus Dressage Culinaire', 'cursus-dressage-culinaire', 'Sublimez vos assiettes comme un chef étoilé.', 48.00, 'seed', 'seed');

-- 4. Leçons officielles (Prix et associations conformes au brief)
INSERT INTO lessons (id, course_id, title, slug, description, content_text, video_url, price, position, created_by, updated_by) VALUES
-- Musique : Guitare
(1, 1, 'Guitare Leçon 1 : Les accords de base', 'guitare-lecon-1', 'Prise en main de l''instrument et premiers accords majeurs.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 26.00, 1, 'seed', 'seed'),
(2, 1, 'Guitare Leçon 2 : Le rythme et arpèges', 'guitare-lecon-2', 'Techniques de main droite pour rythmer vos morceaux.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 26.00, 2, 'seed', 'seed'),

-- Musique : Piano
(3, 2, 'Piano Leçon 1 : Position et clés', 'piano-lecon-1', 'Placement des mains sur le clavier et clé de sol/fa.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis aute irure dolor in reprehenderit.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 26.00, 1, 'seed', 'seed'),
(4, 2, 'Piano Leçon 2 : Premières mélodies', 'piano-lecon-2', 'Coordination des deux mains sur des morceaux simples.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Excepteur sint occaecat cupidatat non proident.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 26.00, 2, 'seed', 'seed'),

-- Informatique : Dev Web
(5, 3, 'Dev Web Leçon 1 : HTML / CSS', 'dev-web-lecon-1', 'Structure sémantique et mise en forme avec CSS moderne.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin nec feugiat lectus, vitae sollicitudin nibh.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 32.00, 1, 'seed', 'seed'),
(6, 3, 'Dev Web Leçon 2 : JavaScript', 'dev-web-lecon-2', 'Dynamiser une interface et manipuler le DOM.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus vel pulvinar dolor.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 32.00, 2, 'seed', 'seed'),

-- Jardinage
(7, 4, 'Jardinage Leçon 1 : Les outils', 'jardinage-lecon-1', 'Choisir et entretenir le matériel essentiel de jardin.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vel felis tempor.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 16.00, 1, 'seed', 'seed'),
(8, 4, 'Jardinage Leçon 2 : Jardiner avec la lune', 'jardinage-lecon-2', 'Comprendre les cycles lunaires pour semer et récolter.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean placerat augue eget tellus.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 16.00, 2, 'seed', 'seed'),

-- Cuisine
(9, 5, 'Cuisine Leçon 1 : Modes de cuisson', 'cuisine-lecon-1', 'Maîtriser la cuisson à l''eau, vapeur, poêle et four.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ut libero eu turpis convallis.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 23.00, 1, 'seed', 'seed'),
(10, 5, 'Cuisine Leçon 2 : Saveurs et assaisonnements', 'cuisine-lecon-2', 'Harmoniser épices, herbes fraîches et sauces.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce volutpat odio sit amet.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 23.00, 2, 'seed', 'seed'),

-- Dressage Culinaire
(11, 6, 'Dressage Leçon 1 : Style dans l''assiette', 'dressage-lecon-1', 'Composition visuelle, contrastes de couleurs et volumes.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tristique magna non.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 26.00, 1, 'seed', 'seed'),
(12, 6, 'Dressage Leçon 2 : Repas gastronomique 4 plats', 'dressage-lecon-2', 'Organisation et dressage pour un menu complet d''exception.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam erat volutpat.', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 26.00, 2, 'seed', 'seed');
