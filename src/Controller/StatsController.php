<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    public function index(ArticleRepository $articles, ReviewRepository $reviews): Response
    {
        $byStatus = $articles->countByStatus();

        return $this->render('stats/index.html.twig', [
            'byStatus' => $byStatus,
            'totalArticles' => array_sum($byStatus),
            'totalReviews' => count($reviews->findAll()),
            'submittedReviews' => count(array_filter($reviews->findAll(), fn ($r) => $r->isSubmitted())),
        ]);
    }

    #[Route('/stats.json', name: 'app_stats_json', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    public function statsJson(ArticleRepository $articles, ReviewRepository $reviews): JsonResponse
    {
        $byStatus = $articles->countByStatus();

        return $this->json([
            'articles_by_status' => $byStatus,
            'total_articles' => array_sum($byStatus),
            'total_reviews' => count($reviews->findAll()),
        ]);
    }
}
