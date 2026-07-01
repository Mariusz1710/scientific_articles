<?php

namespace App\Service;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * @param Article[] $articles
     */
    public function export(array $articles): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($articles) {
            $handle = fopen('php://output', 'w+');
            // UTF-8 BOM so Excel on Windows shows Polish diacritics correctly
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Tytuł', 'Autorzy', 'Dział', 'Status', 'Zgłaszający', 'Data zgłoszenia'], ';');

            foreach ($articles as $article) {
                fputcsv($handle, [
                    $article->getId(),
                    $article->getTitle(),
                    $article->getAuthors(),
                    $article->getCategory()->getName(),
                    $article->getStatus()->label(),
                    $article->getSubmitter()->getFullName(),
                    $article->getSubmittedAt()?->format('Y-m-d H:i') ?? '-',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="artykuly.csv"');

        return $response;
    }
}
