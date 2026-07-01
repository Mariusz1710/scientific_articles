<?php

namespace App\Tests\Unit;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Service\CsvExportService;
use PHPUnit\Framework\TestCase;

class CsvExportServiceTest extends TestCase
{
    public function testExportProducesCsvWithHeaderAndRows(): void
    {
        $category = new Category();
        $category->setName('Informatyka');

        $submitter = new User();
        $submitter->setEmail('autor@example.com');
        $submitter->setFullName('Jan Testowy');

        $article = new Article();
        $article->setTitle('Artykuł testowy');
        $article->setAuthors('Jan Testowy');
        $article->setAbstract('Streszczenie');
        $article->setKeywords('a, b');
        $article->setCategory($category);
        $article->setSubmitter($submitter);

        $service = new CsvExportService();
        $response = $service->export([$article]);

        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        self::assertStringContainsString('Tytuł', $csvContent);
        self::assertStringContainsString('Artykuł testowy', $csvContent);
        self::assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
    }
}
