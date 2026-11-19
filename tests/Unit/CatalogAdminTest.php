<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config\Database;
use App\Core\Slugger;
use App\Repositories\CatalogRepository;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Class CatalogAdminTest
 *
 * Verifies the catalogue write methods used by the backoffice (themes, courses, lessons)
 * and the slug helper. Each test runs inside a transaction that is rolled back.
 *
 * @package Tests\Unit
 */
class CatalogAdminTest extends TestCase
{
    private PDO $db;
    private CatalogRepository $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection();
        $this->db->beginTransaction();
        $this->catalog = new CatalogRepository($this->db);
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        parent::tearDown();
    }

    /**
     * A theme can be created, found by ID, renamed and deleted.
     */
    public function testThemeCrud(): void
    {
        $id = $this->catalog->createTheme(['name' => 'Yoga', 'slug' => 'yoga-test', 'description' => 'Souplesse']);
        $this->assertSame('Yoga', $this->catalog->findThemeById($id)->getName());

        $this->assertTrue($this->catalog->updateTheme($id, ['name' => 'Yoga doux', 'slug' => 'yoga-doux-test', 'description' => '']));
        $this->assertSame('Yoga doux', $this->catalog->findThemeById($id)->getName());

        $this->assertTrue($this->catalog->deleteTheme($id));
        $this->assertNull($this->catalog->findThemeById($id));
    }

    /**
     * Updating a course changes its title, theme and price.
     */
    public function testCourseUpdate(): void
    {
        $this->assertTrue($this->catalog->updateCourse(1, [
            'theme_id' => 2,
            'title' => 'Cursus Guitare Avancé',
            'slug' => 'cursus-guitare-avance-test',
            'description' => 'Niveau avancé',
            'price' => 75.5,
        ]));

        $course = $this->catalog->findCourseById(1);
        $this->assertSame('Cursus Guitare Avancé', $course->getTitle());
        $this->assertSame(2, $course->getThemeId());
        $this->assertSame(75.5, $course->getPrice());
    }

    /**
     * A lesson can be created in a course, updated and deleted.
     */
    public function testLessonCrud(): void
    {
        $id = $this->catalog->createLesson([
            'course_id' => 1,
            'title' => 'Leçon test',
            'slug' => 'lecon-test-' . uniqid(),
            'description' => 'Desc',
            'content_text' => 'Lorem',
            'video_url' => 'https://example.com/video',
            'price' => 12.0,
            'position' => 3,
        ]);

        $lesson = $this->catalog->findLessonById($id);
        $this->assertSame(1, $lesson->getCourseId());
        $this->assertSame(3, $lesson->getPosition());

        $this->assertTrue($this->catalog->updateLesson($id, [
            'course_id' => 2,
            'title' => 'Leçon modifiée',
            'slug' => 'lecon-modifiee-' . uniqid(),
            'description' => '',
            'content_text' => '',
            'video_url' => '',
            'price' => 15.0,
            'position' => 1,
        ]));
        $this->assertSame('Leçon modifiée', $this->catalog->findLessonById($id)->getTitle());
        $this->assertSame(2, $this->catalog->findLessonById($id)->getCourseId());

        $this->assertTrue($this->catalog->deleteLesson($id));
        $this->assertNull($this->catalog->findLessonById($id));
    }

    /**
     * The slug check sees existing slugs, and ignores the row being edited.
     */
    public function testSlugExists(): void
    {
        $this->assertTrue($this->catalog->slugExists('courses', 'cursus-guitare'));
        $this->assertFalse($this->catalog->slugExists('courses', 'cursus-inexistant-xyz'));
        // Editing course 1 must not conflict with its own slug
        $this->assertFalse($this->catalog->slugExists('courses', 'cursus-guitare', 1));
    }

    /**
     * Unknown table names are rejected, so user input can never reach the SQL table name.
     */
    public function testSlugExistsRejectsUnknownTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->catalog->slugExists('users; DROP TABLE users', 'x');
    }

    /**
     * Slugs are lowercase, accent-free and dash-separated.
     */
    public function testSluggerNormalizesTitles(): void
    {
        $this->assertSame('dev-web-html-css', Slugger::make('Dév Web : HTML / CSS !'));
        $this->assertSame('lecon', Slugger::make('leçon'));
        $this->assertSame('', Slugger::make('!!!'));
    }
}
