<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\AccessRepository;
use App\Repositories\CatalogRepository;

/**
 * Class CatalogController
 * 
 * Manages public catalog browsing, themes, courses, and individual lesson views.
 * 
 * @package App\Controllers
 */
class CatalogController extends BaseController
{
    private CatalogRepository $catalogRepository;
    private AccessRepository $accessRepository;

    public function __construct(
        ?CatalogRepository $catalogRepository = null,
        ?AccessRepository $accessRepository = null
    ) {
        $this->catalogRepository = $catalogRepository ?? new CatalogRepository();
        $this->accessRepository = $accessRepository ?? new AccessRepository();
    }

    /**
     * Display full catalog with themes and courses.
     *
     * @return void
     */
    public function index(): void
    {
        $themes = $this->catalogRepository->findAllThemes();
        $courses = $this->catalogRepository->findCourses();

        $this->render('catalog/index', [
            'pageTitle' => 'Catalogue des Formations - Knowledge Learning',
            'themes' => $themes,
            'courses' => $courses
        ]);
    }

    /**
     * Display a specific theme and its courses.
     *
     * @param string $slug
     * @return void
     */
    public function theme(string $slug): void
    {
        $theme = $this->catalogRepository->findThemeBySlug($slug);

        if ($theme === null) {
            http_response_code(404);
            $this->render('errors/404', [
                'pageTitle' => 'Thème introuvable - Knowledge Learning',
                'message' => 'Le thème demandé n\'existe pas.'
            ]);
            return;
        }

        $courses = $this->catalogRepository->findCourses($theme->getId());

        $this->render('catalog/theme', [
            'pageTitle' => sprintf('Thème %s - Knowledge Learning', $theme->getName()),
            'theme' => $theme,
            'courses' => $courses
        ]);
    }

    /**
     * Display a specific course with curriculum and purchase status.
     *
     * @param string $slug
     * @return void
     */
    public function course(string $slug): void
    {
        $course = $this->catalogRepository->findCourseBySlug($slug);

        if ($course === null) {
            http_response_code(404);
            $this->render('errors/404', [
                'pageTitle' => 'Cursus introuvable - Knowledge Learning',
                'message' => 'Le cursus demandé n\'existe pas.'
            ]);
            return;
        }

        $lessons = $this->catalogRepository->findLessonsByCourseId($course->getId());

        $currentUser = Auth::user();
        $hasCourseAccess = false;
        $lessonsAccess = [];

        if ($currentUser !== null) {
            $hasCourseAccess = $this->accessRepository->hasCourseAccess($currentUser->getId(), $course->getId());
            foreach ($lessons as $lesson) {
                $lessonsAccess[$lesson->getId()] = $hasCourseAccess || $this->accessRepository->hasLessonAccess(
                    $currentUser->getId(),
                    $lesson->getId(),
                    $course->getId()
                );
            }
        }

        $this->render('catalog/course', [
            'pageTitle' => sprintf('%s - Knowledge Learning', $course->getTitle()),
            'course' => $course,
            'lessons' => $lessons,
            'hasCourseAccess' => $hasCourseAccess,
            'lessonsAccess' => $lessonsAccess
        ]);
    }

    /**
     * Display lesson content or purchase paywall.
     *
     * @param string $slug
     * @return void
     */
    public function lesson(string $slug): void
    {
        $lesson = $this->catalogRepository->findLessonBySlug($slug);

        if ($lesson === null) {
            http_response_code(404);
            $this->render('errors/404', [
                'pageTitle' => 'Leçon introuvable - Knowledge Learning',
                'message' => 'La leçon demandée n\'existe pas.'
            ]);
            return;
        }

        $course = $this->catalogRepository->findCourseById($lesson->getCourseId());
        if ($course !== null) {
            $courseLessons = $this->catalogRepository->findLessonsByCourseId($course->getId());
            $course->setLessons($courseLessons);
        }

        $currentUser = Auth::user();
        $hasAccess = false;
        $isValidated = false;

        if ($currentUser !== null && $course !== null) {
            $hasAccess = $this->accessRepository->hasLessonAccess($currentUser->getId(), $lesson->getId(), $course->getId());
            $isValidated = $this->accessRepository->isLessonValidated($currentUser->getId(), $lesson->getId());
        }

        $this->render('catalog/lesson', [
            'pageTitle' => sprintf('%s - Knowledge Learning', $lesson->getTitle()),
            'lesson' => $lesson,
            'course' => $course,
            'hasAccess' => $hasAccess,
            'isValidated' => $isValidated
        ]);
    }
}
