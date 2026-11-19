<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Class AccessRepository
 * 
 * DAO handling user access permissions to purchased courses and lessons.
 * 
 * @package App\Repositories
 */
class AccessRepository extends BaseRepository
{
    /**
     * Check if a user has access to a specific course.
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    public function hasCourseAccess(int $userId, int $courseId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM user_courses WHERE user_id = :user_id AND course_id = :course_id LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Check if a user has access to a specific lesson.
     * User has access if they purchased the individual lesson OR the entire parent course.
     *
     * @param int $userId
     * @param int $lessonId
     * @param int $courseId
     * @return bool
     */
    public function hasLessonAccess(int $userId, int $lessonId, int $courseId): bool
    {
        // 1. Direct course purchase
        if ($this->hasCourseAccess($userId, $courseId)) {
            return true;
        }

        // 2. Individual lesson purchase
        $stmt = $this->db->prepare('SELECT 1 FROM user_lessons WHERE user_id = :user_id AND lesson_id = :lesson_id LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'lesson_id' => $lessonId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Check if a lesson has already been validated by the user.
     *
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function isLessonValidated(int $userId, int $lessonId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM lesson_validations WHERE user_id = :user_id AND lesson_id = :lesson_id LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'lesson_id' => $lessonId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Grant access to a full course.
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    public function grantCourseAccess(int $userId, int $courseId): bool
    {
        $sql = 'INSERT INTO user_courses (user_id, course_id, created_by, updated_by)
                VALUES (:user_id, :course_id, :created_by, :updated_by)
                ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'created_by' => 'order_fulfillment',
            'updated_by' => 'order_fulfillment'
        ]);
    }

    /**
     * Grant access to an individual lesson.
     *
     * @param int $userId
     * @param int $lessonId
     * @return bool
     */
    public function grantLessonAccess(int $userId, int $lessonId): bool
    {
        $sql = 'INSERT INTO user_lessons (user_id, lesson_id, created_by, updated_by)
                VALUES (:user_id, :lesson_id, :created_by, :updated_by)
                ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'created_by' => 'order_fulfillment',
            'updated_by' => 'order_fulfillment'
        ]);
    }
}
