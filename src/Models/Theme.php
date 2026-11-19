<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Theme
 * 
 * Represents a knowledge training theme (e.g. Musique, Informatique, etc.).
 * 
 * @package App\Models
 */
class Theme
{
    private ?int $id;
    private string $name;
    private string $slug;
    private ?string $description;

    public function __construct(array $attributes = [])
    {
        $this->id = isset($attributes['id']) ? (int) $attributes['id'] : null;
        $this->name = (string) ($attributes['name'] ?? '');
        $this->slug = (string) ($attributes['slug'] ?? '');
        $this->description = $attributes['description'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
}
