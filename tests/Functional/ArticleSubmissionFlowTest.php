<?php

namespace App\Tests\Functional;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArticleSubmissionFlowTest extends WebTestCase
{
    public function testUserCanRegisterLoginAndSubmitAnArticleDraft(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        // A category must exist for the submission form to be usable.
        $category = new Category();
        $category->setName('Informatyka testowa');
        $em->persist($category);
        $em->flush();

        // --- Register ---
        $client->request('GET', '/register');
        $client->submitForm('Utwórz konto', [
            'registration_form[fullName]' => 'Test User',
            'registration_form[email]' => 'test.user@example.com',
            'registration_form[plainPassword][first]' => 'password123',
            'registration_form[plainPassword][second]' => 'password123',
        ]);
        self::assertResponseRedirects('/login');

        // --- Login ---
        $client->followRedirect();
        $client->submitForm('Zaloguj się', [
            '_username' => 'test.user@example.com',
            '_password' => 'password123',
        ]);
        self::assertResponseRedirects('/');

        // --- Submit a draft article with a LaTeX source file ---
        $tmpFile = tempnam(sys_get_temp_dir(), 'tex').'.tex';
        file_put_contents($tmpFile, '\documentclass{article}\begin{document}Test\end{document}');
        $uploadedFile = new UploadedFile($tmpFile, 'artykul.tex', 'text/plain', null, true);

        $client->request('GET', '/articles/new');
        $client->submitForm('Zapisz jako wersję roboczą', [
            'article[title]' => 'Testowy artykuł zgłoszony automatycznie',
            'article[authors]' => 'Test User',
            'article[abstract]' => 'Streszczenie testowe.',
            'article[keywords]' => 'test, ci',
            'article[category]' => $category->getId(),
            'article[file]' => $uploadedFile,
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Testowy artykuł zgłoszony automatycznie');

        @unlink($tmpFile);
    }
}
