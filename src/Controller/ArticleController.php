<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Enum\ArticleStatus;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/articles')]
#[IsGranted('ROLE_AUTHOR')]
class ArticleController extends AbstractController
{
    #[Route('', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articles): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('article/index.html.twig', [
            'articles' => $articles->findBy(['submitter' => $user], ['updatedAt' => 'DESC']),
        ]);
    }

    #[Route('/published', name: 'app_article_published', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function published(ArticleRepository $articles): Response
    {
        return $this->render('article/published.html.twig', [
            'articles' => $articles->findPublished(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, FileUploadService $uploads): Response
    {
        $article = new Article();
        $article->setSubmitter($this->getUser());

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $uploads->storeArticleFile($article, $file);
                }
            } catch (FileException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->render('article/new.html.twig', ['form' => $form]);
            }

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Zgłoszenie zapisane jako wersja robocza. Możesz je edytować przed przesłaniem do recenzji.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        $this->denyAccessUnlessOwnerOrStaff($article);

        return $this->render('article/show.html.twig', ['article' => $article]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $em, FileUploadService $uploads): Response
    {
        $this->denyAccessUnlessOwner($article);

        if (!$article->isEditable()) {
            $this->addFlash('error', 'Tego zgłoszenia nie można już edytować - zostało przesłane do recenzji.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        $form = $this->createForm(ArticleType::class, $article, ['file_required' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $uploads->storeArticleFile($article, $file);
                }
            } catch (FileException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->render('article/edit.html.twig', ['form' => $form, 'article' => $article]);
            }

            $article->touch();
            $em->flush();

            $this->addFlash('success', 'Zgłoszenie zaktualizowane.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/edit.html.twig', ['form' => $form, 'article' => $article]);
    }

    #[Route('/{id}/submit', name: 'app_article_submit', methods: ['POST'])]
    public function submit(Article $article, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessOwner($article);

        if (!$article->isEditable()) {
            $this->addFlash('error', 'Zgłoszenie zostało już przesłane.');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        if (null === $article->getCurrentFilePath()) {
            $this->addFlash('error', 'Dołącz plik artykułu przed przesłaniem do recenzji.');

            return $this->redirectToRoute('app_article_edit', ['id' => $article->getId()]);
        }

        $article->markSubmitted();
        $em->flush();

        $this->addFlash('success', 'Artykuł przesłany do recenzji.');

        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }

    private function denyAccessUnlessOwner(Article $article): void
    {
        if ($article->getSubmitter() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
    }

    private function denyAccessUnlessOwnerOrStaff(Article $article): void
    {
        if ($article->getSubmitter() === $this->getUser()
            || $this->isGranted('ROLE_REVIEWER')
            || $this->isGranted('ROLE_EDITOR')) {
            return;
        }

        throw $this->createAccessDeniedException();
    }
}

foreach(workers as worker)
{
	echo "This is a communicate";
}
