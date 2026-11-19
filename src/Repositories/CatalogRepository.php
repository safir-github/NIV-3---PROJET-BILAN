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

    /**
     * Update the editable fields of a course.
     *
     * @param int   $id
     * @param array $data Keys: theme_id, title, slug, description, price
     * @return bool
     */
    public function updateCourse(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE courses
             SET theme_id = :theme_id, title = :title, slug = :slug, description = :description, price = :price, updated_by = :updated_by
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'theme_id' => $data['theme_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'updated_by' => 'admin',
        ]);
    }

    /**
     * Find a theme by its primary key.
     *
     * @param int $id
     * @return Theme|null
     */
    public function findThemeById(int $id): ?Theme
    {
        $stmt = $this->db->prepare('SELECT * FROM themes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Theme($row) : null;
    }

    /**
     * Create a theme.
     *
     * @param array $data Keys: name, slug, description
     * @return int The new theme ID
     */
    public function createTheme(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO themes (name, slug, description, created_by, updated_by)
             VALUES (:name, :slug, :description, :created_by, :updated_by)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'created_by' => 'admin',
            'updated_by' => 'admin',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a theme.
     *
     * @param int   $id
     * @param array $data Keys: name, slug, description
     * @return bool
     */
    public function updateTheme(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE themes SET name = :name, slug = :slug, description = :description, updated_by = :updated_by WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'updated_by' => 'admin',
        ]);
    }

    /**
     * Delete a theme. Its courses, lessons and certifications are removed by ON DELETE CASCADE.
     *
     * @param int $id
     * @return bool
     */
    public function deleteTheme(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM themes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Create a lesson.
     *
     * @param array $data Keys: course_id, title, slug, description, content_text, video_url, price, position
     * @return int The new lesson ID
     */
    public function createLesson(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lessons (course_id, title, slug, description, content_text, video_url, price, position, created_by, updated_by)
             VALUES (:course_id, :title, :slug, :description, :content_text, :video_url, :price, :position, :created_by, :updated_by)'
        );
        $stmt->execute([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'content_text' => $data['content_text'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'price' => $data['price'],
            'position' => $data['position'] ?? 1,
            'created_by' => 'admin',
            'updated_by' => 'admin',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a lesson.
     *
     * @param int   $id
     * @param array $data Same keys as createLesson()
     * @return bool
     */
    public function updateLesson(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE lessons
             SET course_id = :course_id, title = :title, slug = :slug, description = :description,
                 content_text = :content_text, video_url = :video_url, price = :price, position = :position, updated_by = :updated_by
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'content_text' => $data['content_text'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'price' => $data['price'],
            'position' => $data['position'] ?? 1,
            'updated_by' => 'admin',
        ]);
    }

    /**
     * Delete a lesson.
     *
     * @param int $id
     * @return bool
     */
    public function deleteLesson(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM lessons WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check whether a slug is already used in a given table.
     *
     * @param string   $table   Either 'themes', 'courses' or 'lessons'
     * @param string   $slug
     * @param int|null $exceptId ID to ignore (the row being updated)
     * @return bool
     */
    public function slugExists(string $table, string $slug, ?int $exceptId = null): bool
    {
        // Only fixed table names are accepted, never user input
        if (!in_array($table, ['themes', 'courses', 'lessons'], true)) {
            throw new \InvalidArgumentException('Unknown table: ' . $table);
        }

        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = :slug";
        $params = ['slug' => $slug];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
