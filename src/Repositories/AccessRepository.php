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
}
