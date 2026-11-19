<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class Slugger
 *
 * Turns a human title into a URL-friendly slug (e.g. "Dév Web !" => "dev-web").
 * Used by the backoffice when creating or renaming themes, courses and lessons.
 *
 * @package App\Core
 */
class Slugger
{
    /**
     * Build a slug from any text.
     *
     * @param string $text
     * @return string Lowercase ASCII letters, digits and dashes. Empty if the text has no usable character.
     */
    public static function make(string $text): string
    {
        // Remove accents (é => e) when the iconv extension can transliterate
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii === false) {
            $ascii = $text;
        }

        // Some transliterations leave accent marks behind (é => 'e): drop them so they do not become dashes
        $ascii = str_replace(["'", '"', '`', '^', '~', '?'], '', $ascii);

        $slug = strtolower($ascii);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}
