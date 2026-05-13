<?php

namespace App\Support;

class FileSanitizer
{
    /**
     * Replace all whitespace in filename with underscore and remove duplicate underscores.
     * Keep other characters intact to avoid unexpected behavior.
     */
    public static function sanitize(string $filename): string
    {
        // Replace any whitespace (spaces, tabs) with underscore
        $sanitized = preg_replace('/\s+/', '_', $filename);
        // Remove any duplicate underscores
        $sanitized = preg_replace('/_+/', '_', $sanitized);
        return $sanitized;
    }
}
