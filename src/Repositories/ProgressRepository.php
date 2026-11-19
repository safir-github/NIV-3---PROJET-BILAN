<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Class ProgressRepository
 * 
 * DAO component managing lesson validations and curriculum completion calculations.
 * 
 * @package App\Repositories
 */
class ProgressRepository extends BaseRepository
{
    /**
     * Record validation for a specific lesson by a user (idempotent via ON DUPLICATE KEY).
     *
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function validateLesson(int $userId, int $lessonId): bool
    {
        $sql = 'INSERT INTO lesson_validations (user_id, lesson_id, created_by, updated_by)
                VALUES (:user_id, :lesson_id, :created_by, :updated_by)
                ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'created_by' => 'student_validation',
            'updated_by' => 'student_validation'
        ]);
    }

    /**
     * Check if all lessons of a course have been validated by the user.
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    public function isCourseCompleted(int $userId, int $courseId): bool
    {
        // Total lessons in the course
        $stmtTotal = $this->db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id');
        $stmtTotal->execute(['course_id' => $courseId]);
        $totalLessons = (int) $stmtTotal->fetchColumn();

        if ($totalLessons === 0) {
            return false;
        }

        // Count validated lessons in this course
        $sqlValidated = 'SELECT COUNT(*) 
                         FROM lesson_validations lv
                         JOIN lessons l ON lv.lesson_id = l.id
                         WHERE lv.user_id = :user_id AND l.course_id = :course_id';

        $stmtValidated = $this->db->prepare($sqlValidated);
        $stmtValidated->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
        $validatedLessons = (int) $stmtValidated->fetchColumn();

        return $validatedLessons >= $totalLessons;
    }

    /**
     * Check if every lesson of every course in a theme has been validated by the user.
     * This is the rule for the theme certification in the brief.
     *
     * @param int $userId
     * @param int $themeId
     * @return bool
     */
    public function isThemeCompleted(int $userId, int $themeId): bool
    {
        $sqlTotal = 'SELECT COUNT(*)
                     FROM lessons l
                     JOIN courses c ON l.course_id = c.id
                     WHERE c.theme_id = :theme_id';
        $stmtTotal = $this->db->prepare($sqlTotal);
        $stmtTotal->execute(['theme_id' => $themeId]);
        $totalLessons = (int) $stmtTotal->fetchColumn();

        if ($totalLessons === 0) {
            return false;
        }

        $sqlValidated = 'SELECT COUNT(*)
                         FROM lesson_validations lv
                         JOIN lessons l ON lv.lesson_id = l.id
                         JOIN courses c ON l.course_id = c.id
                         WHERE lv.user_id = :user_id AND c.theme_id = :theme_id';
        $stmtValidated = $this->db->prepare($sqlValidated);
        $stmtValidated->execute([
            'user_id' => $userId,
            'theme_id' => $themeId
        ]);
        $validatedLessons = (int) $stmtValidated->fetchColumn();

        return $validatedLessons >= $totalLessons;
    }

    /**
     * Find the theme a course belongs to.
     *
     * @param int $courseId
     * @return int|null
     */
    public function findThemeIdForCourse(int $courseId): ?int
    {
        $stmt = $this->db->prepare('SELECT theme_id FROM courses WHERE id = :course_id LIMIT 1');
        $stmt->execute(['course_id' => $courseId]);
        $themeId = $stmt->fetchColumn();

        return $themeId === false ? null : (int) $themeId;
    }

    /**
     * Record that a user has validated a whole course (idempotent).
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    public function recordCourseValidation(int $userId, int $courseId): bool
    {
        $sql = 'INSERT INTO course_validations (user_id, course_id, created_by, updated_by)
                VALUES (:user_id, :course_id, :created_by, :updated_by)
                ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'created_by' => 'course_completion',
            'updated_by' => 'course_completion'
        ]);
    }

    /**
     * Check whether the user has already validated the whole course.
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    public function hasValidatedCourse(int $userId, int $courseId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM course_validations WHERE user_id = :user_id AND course_id = :course_id');
        $stmt->execute(['user_id' => $userId, 'course_id' => $courseId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
