-- =====================================================================
-- Migration 002 : certifications par thème + cursus validés
-- Apply on an existing database that was created with the previous schema.
-- Fresh installs use database/schema.sql directly.
-- =====================================================================

-- 1. New table storing completed courses
CREATE TABLE IF NOT EXISTS course_validations (
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

-- 2. Certifications now belong to a theme instead of a course.
--    Existing rows are removed first because they cannot be mapped to a theme automatically.
DELETE FROM certifications;

ALTER TABLE certifications DROP FOREIGN KEY fk_cert_course;
-- The foreign key on user_id needs its own index before the composite unique key can go
ALTER TABLE certifications ADD INDEX idx_cert_user (user_id);
ALTER TABLE certifications DROP INDEX uq_user_course_cert;
ALTER TABLE certifications DROP COLUMN course_id;
ALTER TABLE certifications ADD COLUMN theme_id INT NOT NULL AFTER user_id;
ALTER TABLE certifications ADD UNIQUE KEY uq_user_theme_cert (user_id, theme_id);
ALTER TABLE certifications ADD CONSTRAINT fk_cert_theme FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE;
