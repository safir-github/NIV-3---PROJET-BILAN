<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Theme;
use App\Models\Course;
use App\Models\Lesson;

/**
 * Class CatalogRepository
 * 
 * DAO component handling database queries for Themes, Courses, and Lessons.
 * 
 * @package App\Repositories
 */
class CatalogRepository extends BaseRepository
{
    /**
     * Get all training themes.
     * 
     * @return Theme[]
     */
    public function findAllThemes(): array
    {
        $stmt = $this->db->query('SELECT * FROM themes ORDER BY id ASC');
        $themes = [];
        while ($row = $stmt->fetch()) {
            $themes[] = new Theme($row);
        }
        return $themes;
    }

    /**
     * Find a theme by its slug.
     * 
     * @param string $slug
     * @return Theme|null
     */
    public function findThemeBySlug(string $slug): ?Theme
    {
        $stmt = $this->db->prepare('SELECT * FROM themes WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? new Theme($row) : null;
    }

    /**
     * Find all courses, optionally filtered by theme ID.
     * 
     * @param int|null $themeId
     * @return Course[]
     */
    public function findCourses(?int $themeId = null): array
    {
        $sql = 'SELECT c.*, t.name as theme_name 
                FROM courses c 
                JOIN themes t ON c.theme_id = t.id';

        if ($themeId !== null) {
            $sql .= ' WHERE c.theme_id = :theme_id';
            $stmt = $this->db->prepare($sql . ' ORDER BY c.id ASC');
            $stmt->execute(['theme_id' => $themeId]);
        } else {
            $stmt = $this->db->query($sql . ' ORDER BY c.id ASC');
        }

        $courses = [];
        while ($row = $stmt->fetch()) {
            $courses[] = new Course($row);
        }
        return $courses;
    }

    /**
     * Find a single course by slug.
     * 
     * @param string $slug
     * @return Course|null
     */
    public function findCourseBySlug(string $slug): ?Course
    {
        $stmt = $this->db->prepare('SELECT c.*, t.name as theme_name 
                                   FROM courses c 
                                   JOIN themes t ON c.theme_id = t.id 
                                   WHERE c.slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? new Course($row) : null;
    }

    /**
     * Find a single course by ID.
     * 
     * @param int $id
     * @return Course|null
     */
    public function findCourseById(int $id): ?Course
    {
        $stmt = $this->db->prepare('SELECT c.*, t.name as theme_name 
                                   FROM courses c 
                                   JOIN themes t ON c.theme_id = t.id 
                                   WHERE c.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Course($row) : null;
    }

    /**
     * Find all lessons for a specific course ID.
     * 
     * @param int $courseId
     * @return Lesson[]
     */
    public function findLessonsByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM lessons WHERE course_id = :course_id ORDER BY position ASC');
        $stmt->execute(['course_id' => $courseId]);
        $lessons = [];
        while ($row = $stmt->fetch()) {
            $lessons[] = new Lesson($row);
        }
        return $lessons;
    }

    /**
     * Find a single lesson by slug.
     * 
     * @param string $slug
     * @return Lesson|null
     */
    public function findLessonBySlug(string $slug): ?Lesson
    {
        $stmt = $this->db->prepare('SELECT * FROM lessons WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? new Lesson($row) : null;
    }

    /**
     * Find a single lesson by ID.
     * 
     * @param int $id
     * @return Lesson|null
     */
    public function findLessonById(int $id): ?Lesson
    {
        $stmt = $this->db->prepare('SELECT * FROM lessons WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Lesson($row) : null;
    }

    /**
     * Create a new course.
     *
     * @param array<string, mixed> $data
     * @return int
     */
    public function createCourse(array $data): int
    {
        $sql = 'INSERT INTO courses (theme_id, title, slug, description, price, created_by, updated_by)
                VALUES (:theme_id, :title, :slug, :description, :price, :created_by, :updated_by)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'theme_id' => $data['theme_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'created_by' => $data['created_by'] ?? 'admin',
            'updated_by' => $data['updated_by'] ?? 'admin'
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete a course.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCourse(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM courses WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
