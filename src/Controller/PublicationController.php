<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\NotificationService;
use App\Service\PdfExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/publication')]
#[IsGranted('ROLE_EDITOR')]
class PublicationController extends AbstractController
{
    #[Route('', name: 'app_publication_index', methods: ['GET'])]
    public function index(ArticleRepository $articles): Response
    {
        return $this->render('publication/index.html.twig', [
            'articles' => $articles->findAwaitingEditorDecision(),
        ]);
    }

    #[Route('/{id}/approve', name: 'app_publication_approve', methods: ['POST'])]
    public function approve(Article $article, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        $article->markPublished();
        $em->flush();
        $notifications->notifyArticleStatusChanged($article);

        $this->addFlash('success', 'Artykuł opublikowany.');

        return $this->redirectToRoute('app_publication_index');
    }

    #[Route('/{id}/print', name: 'app_publication_print', methods: ['GET'])]
    public function printVersion(Article $article, PdfExportService $pdf): Response
    {
        return $pdf->renderArticle($article);
    }
}
