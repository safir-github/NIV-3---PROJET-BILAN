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
}
