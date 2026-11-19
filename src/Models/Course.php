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

    public function getId(): ?int { return $this->id; }
    public function getThemeId(): int { return $this->themeId; }
    public function getTitle(): string { return $this->title; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getThemeName(): ?string { return $this->themeName; }
}
