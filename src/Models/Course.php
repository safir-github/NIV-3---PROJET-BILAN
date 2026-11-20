<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Course
 * 
 * Represents a training cursus (e.g. Cursus Guitare, Cursus Dev Web).
 * 
 * @package App\Models
 */
class Course
{
    private ?int $id;
    private int $themeId;
    private string $title;
    private string $slug;
    private ?string $description;
    private float $price;
    private ?string $themeName;
    /** @var Lesson[] */
    private array $lessons = [];

    /**
     * Create a new object.
     */
    public function __construct(array $attributes = [])
    {
        $this->id = isset($attributes['id']) ? (int) $attributes['id'] : null;
        $this->themeId = (int) ($attributes['theme_id'] ?? 0);
        $this->title = (string) ($attributes['title'] ?? '');
        $this->slug = (string) ($attributes['slug'] ?? '');
        $this->description = $attributes['description'] ?? null;
        $this->price = (float) ($attributes['price'] ?? 0.0);
        $this->themeName = $attributes['theme_name'] ?? null;
    }

    /**
     * Get the id.
     */
    public function getId(): ?int { return $this->id; }
    /**
     * Get the theme id.
     */
    public function getThemeId(): int { return $this->themeId; }
    /**
     * Get the title.
     */
    public function getTitle(): string { return $this->title; }
    /**
     * Get the slug.
     */
    public function getSlug(): string { return $this->slug; }
    /**
     * Get the description.
     */
    public function getDescription(): ?string { return $this->description; }
    /**
     * Get the price.
     */
    public function getPrice(): float { return $this->price; }
    /**
     * Get the theme name.
     */
    public function getThemeName(): ?string { return $this->themeName; }

    /**
     * @return Lesson[]
     */
    public function getLessons(): array { return $this->lessons; }

    /**
     * @param Lesson[] $lessons
     * @return self
     */
    public function setLessons(array $lessons): self { $this->lessons = $lessons; return $this; }
}
