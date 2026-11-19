-- =====================================================================
-- Knowledge Learning - Physical Data Model (MPD) / Database Schema
-- Level 3 Web Developer Final Project (CEF)
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS certifications;
DROP TABLE IF EXISTS course_validations;
DROP TABLE IF EXISTS lesson_validations;
DROP TABLE IF EXISTS user_lessons;
DROP TABLE IF EXISTS user_courses;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS themes;:
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. Table: users
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'ROLE_CLIENT',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    activation_token VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Table: themes
-- ---------------------------------------------------------------------
CREATE TABLE themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Table: courses (Cursus)
-- ---------------------------------------------------------------------
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    CONSTRAINT fk_courses_theme FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Table: lessons (Leçons)
-- ---------------------------------------------------------------------
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    content_text LONGTEXT NULL,
    video_url VARCHAR(255) NULL,
    price DECIMAL(10, 2) NOT NULL,
    position INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Table: orders (Commandes E-commerce)
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_reference VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending', -- pending, paid, cancelled
    stripe_session_id VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Table: order_items (Lignes de commande)
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_type VARCHAR(20) NOT NULL, -- 'course' or 'lesson'
    course_id INT NULL,
    lesson_id INT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_order_items_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. Table: user_courses (Droits d'accès aux cursus achetés)
-- ---------------------------------------------------------------------
CREATE TABLE user_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    UNIQUE KEY uq_user_course (user_id, course_id),
    CONSTRAINT fk_uc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_uc_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. Table: user_lessons (Droits d'accès aux leçons individuelles achetées)
-- ---------------------------------------------------------------------
CREATE TABLE user_lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    UNIQUE KEY uq_user_lesson (user_id, lesson_id),
    CONSTRAINT fk_ul_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ul_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. Table: lesson_validations (Suivi de progression par leçon)
-- ---------------------------------------------------------------------
CREATE TABLE lesson_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    validated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    UNIQUE KEY uq_user_lesson_validation (user_id, lesson_id),
    CONSTRAINT fk_lv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_lv_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. Table: course_validations (Cursus validé quand toutes ses leçons sont validées)
-- ---------------------------------------------------------------------
CREATE TABLE course_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    validated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    UNIQUE KEY uq_user_course_validation (user_id, course_id),
    CONSTRAINT fk_cv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cv_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. Table: certifications (Certification obtenue quand toutes les leçons d'un thème sont validées)
-- ---------------------------------------------------------------------
CREATE TABLE certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme_id INT NOT NULL,
    certificate_code VARCHAR(100) NOT NULL UNIQUE,
    issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL DEFAULT 'system',
    updated_by VARCHAR(100) NOT NULL DEFAULT 'system',
    UNIQUE KEY uq_user_theme_cert (user_id, theme_id),
    CONSTRAINT fk_cert_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cert_theme FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
