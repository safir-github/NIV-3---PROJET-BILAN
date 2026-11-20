<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Lesson
 * 
 * Represents an individual lesson within a cursus.
 * 
 * @package App\Models
 */
class Lesson
{
    private ?int $id;
    private int $courseId;
    private string $title;
    private string $slug;
    private ?string $description;
    private ?string $contentText;
    private ?string $videoUrl;
    private float $price;
    private int $position;

    /**
     * Create a new object.
     */
    public function __construct(array $attributes = [])
    {
        $this->id = isset($attributes['id']) ? (int) $attributes['id'] : null;
        $this->courseId = (int) ($attributes['course_id'] ?? 0);
        $this->title = (string) ($attributes['title'] ?? '');
        $this->slug = (string) ($attributes['slug'] ?? '');
        $this->description = $attributes['description'] ?? null;
        $this->contentText = $attributes['content_text'] ?? null;
        $this->videoUrl = $attributes['video_url'] ?? null;
        $this->price = (float) ($attributes['price'] ?? 0.0);
        $this->position = (int) ($attributes['position'] ?? 1);
    }

    /**
     * Get the id.
     */
    public function getId(): ?int { return $this->id; }
    /**
     * Get the course id.
     */
    public function getCourseId(): int { return $this->courseId; }
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
     * Get the content text.
     */
    public function getContentText(): ?string { return $this->contentText; }
    /**
     * Get the video url.
     */
    public function getVideoUrl(): ?string { return $this->videoUrl; }
    /**
     * Get the price.
     */
    public function getPrice(): float { return $this->price; }
    /**
     * Get the position.
     */
    public function getPosition(): int { return $this->position; }
}
