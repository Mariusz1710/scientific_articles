<?php

namespace App\Tests\Unit;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Enum\ArticleStatus;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private function buildArticle(): Article
    {
        $category = new Category();
        $category->setName('Informatyka');

        $submitter = new User();
        $submitter->setEmail('autor@example.com');
        $submitter->setFullName('Jan Testowy');

        $article = new Article();
        $article->setTitle('Tytuł testowy');
        $article->setAuthors('Jan Testowy');
        $article->setAbstract('Streszczenie testowe.');
        $article->setKeywords('test, artykuł, jednostkowy');
        $article->setCategory($category);
        $article->setSubmitter($submitter);

        return $article;
    }

    public function testNewArticleIsDraftAndEditable(): void
    {
        $article = $this->buildArticle();

        self::assertSame(ArticleStatus::DRAFT, $article->getStatus());
        self::assertTrue($article->isEditable());
    }

    public function testMarkSubmittedChangesStatusAndLocksEditing(): void
    {
        $article = $this->buildArticle();

        $article->markSubmitted();

        self::assertSame(ArticleStatus::SUBMITTED, $article->getStatus());
        self::assertNotNull($article->getSubmittedAt());
        self::assertFalse($article->isEditable());
    }

    public function testMarkPublishedSetsPublishedAt(): void
    {
        $article = $this->buildArticle();

        $article->markPublished();

        self::assertSame(ArticleStatus::PUBLISHED, $article->getStatus());
        self::assertNotNull($article->getPublishedAt());
    }

    public function testKeywordsListIsParsedAndTrimmed(): void
    {
        $article = $this->buildArticle();
        $article->setKeywords(' php ,  symfony,  testy ');

        self::assertSame(['php', 'symfony', 'testy'], $article->getKeywordsList());
    }
}
