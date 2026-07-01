<?php

namespace App\Enum;

enum ArticleFileFormat: string
{
    case LATEX = 'latex';
    case PDF = 'pdf';
    case DOCX = 'docx';

    public static function fromExtension(string $extension): ?self
    {
        return match (strtolower($extension)) {
            'tex' => self::LATEX,
            'pdf' => self::PDF,
            'docx' => self::DOCX,
            default => null,
        };
    }
}
