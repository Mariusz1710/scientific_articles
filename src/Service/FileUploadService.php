<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\ArticleVersion;
use App\Enum\ArticleFileFormat;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileUploadService
{
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/x-tex',
        'text/x-tex',
        'text/plain', // some browsers report .tex as text/plain
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private const MAX_SIZE_BYTES = 25 * 1024 * 1024; // 25MB

    public function __construct(private readonly string $uploadDirectory)
    {
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0775, true);
        }
    }

    /**
     * Validates and stores an uploaded article file, returning a new ArticleVersion.
     * Throws FileException on invalid format/size.
     */
    public function storeArticleFile(Article $article, UploadedFile $file): ArticleVersion
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?? '');
        $format = ArticleFileFormat::fromExtension($extension);

        if (null === $format) {
            throw new FileException('Niedozwolony format pliku. Dozwolone są: .tex, .pdf, .docx.');
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new FileException('Plik jest zbyt duży. Maksymalny rozmiar to 25 MB.');
        }

        $mimeType = $file->getMimeType();
        if (null !== $mimeType && !in_array($mimeType, self::ALLOWED_MIME_TYPES, true) && ArticleFileFormat::LATEX !== $format) {
            // LaTeX source files are inconsistently reported by browsers, so we only
            // strictly enforce MIME type for PDF/DOCX where the risk of a mismatched
            // extension is higher.
            throw new FileException('Nieprawidłowy typ pliku dla rozszerzenia '.$extension.'.');
        }

        $slugger = new AsciiSlugger();
        $safeName = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $newFileName = sprintf('%s-%s.%s', $safeName, uniqid(), $extension);

        $file->move($this->uploadDirectory, $newFileName);

        $version = new ArticleVersion();
        $version->setFilePath('uploads/articles/'.$newFileName);
        $version->setOriginalFileName($file->getClientOriginalName());
        $version->setFormat($format);

        $article->addVersion($version);
        $article->setCurrentFilePath($version->getFilePath());
        $article->setCurrentFileFormat($format);
        $article->touch();

        return $version;
    }
}
