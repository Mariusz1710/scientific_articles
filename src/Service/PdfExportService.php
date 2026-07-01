<?php

namespace App\Service;

use App\Entity\Article;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates print-ready PDF documents using mPDF (works from plain HTML,
 * no external binary needed, plays nicely with the containerized setup).
 */
class PdfExportService
{
    /**
     * Print-ready single-article summary (title, authors, abstract, status, reviews summary).
     */
    public function renderArticle(Article $article): Response
    {
        $html = $this->wrapHtml(sprintf(
            '<h1>%s</h1>
            <p><strong>Autorzy:</strong> %s</p>
            <p><strong>Dział naukowy:</strong> %s</p>
            <p><strong>Słowa kluczowe:</strong> %s</p>
            <p><strong>Status:</strong> %s</p>
            <h2>Streszczenie</h2>
            <p>%s</p>',
            htmlspecialchars($article->getTitle()),
            htmlspecialchars($article->getAuthors()),
            htmlspecialchars($article->getCategory()->getName()),
            htmlspecialchars($article->getKeywords()),
            htmlspecialchars($article->getStatus()->label()),
            nl2br(htmlspecialchars($article->getAbstract()))
        ));

        return $this->render($html, 'artykul-'.$article->getId().'.pdf');
    }

    /**
     * @param Article[] $articles
     */
    public function renderArticleList(array $articles): Response
    {
        $rows = '';
        foreach ($articles as $article) {
            $rows .= sprintf(
                '<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $article->getId(),
                htmlspecialchars($article->getTitle()),
                htmlspecialchars($article->getAuthors()),
                htmlspecialchars($article->getCategory()->getName()),
                htmlspecialchars($article->getStatus()->label())
            );
        }

        $html = $this->wrapHtml(sprintf(
            '<h1>Lista artykułów</h1>
            <table width="100%%" border="1" cellpadding="4" cellspacing="0">
                <thead><tr><th>ID</th><th>Tytuł</th><th>Autorzy</th><th>Dział</th><th>Status</th></tr></thead>
                <tbody>%s</tbody>
            </table>',
            $rows
        ));

        return $this->render($html, 'lista-artykulow.pdf');
    }

    private function wrapHtml(string $body): string
    {
        return '<html><head><meta charset="UTF-8"><style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            h1 { font-size: 18px; } h2 { font-size: 14px; }
            table { border-collapse: collapse; }
            th, td { border: 1px solid #999; padding: 4px; }
        </style></head><body>'.$body.'</body></html>';
    }

    private function render(string $html, string $filename): Response
    {
        $mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'tempDir' => sys_get_temp_dir().'/mpdf',
]);
        $mpdf->WriteHTML($html);
        $content = $mpdf->Output('', 'S');

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
