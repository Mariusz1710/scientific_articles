<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Application monitoring endpoints.
 *
 * /health  -> liveness/readiness probe (checks DB connectivity), used by
 *             Docker healthchecks / orchestrators.
 * /metrics -> Prometheus text exposition format, scraped by the `prometheus`
 *             service defined in docker-compose.yml (see monitoring/prometheus.yml).
 */
class HealthController extends AbstractController
{
    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function health(Connection $connection): JsonResponse
    {
        try {
            $connection->executeQuery('SELECT 1');
            $dbStatus = 'ok';
            $httpCode = 200;
        } catch (\Throwable $e) {
            $dbStatus = 'error';
            $httpCode = 503;
        }

        return $this->json([
            'status' => 'error' === $dbStatus ? 'down' : 'up',
            'database' => $dbStatus,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ], $httpCode);
    }

    #[Route('/metrics', name: 'app_metrics', methods: ['GET'])]
    public function metrics(ArticleRepository $articles, ReviewRepository $reviews, UserRepository $users): Response
    {
        $byStatus = $articles->countByStatus();

        $lines = [];
        $lines[] = '# HELP articles_total Number of articles per status';
        $lines[] = '# TYPE articles_total gauge';
        foreach ($byStatus as $status => $count) {
            $lines[] = sprintf('articles_total{status="%s"} %d', $status, $count);
        }

        $lines[] = '# HELP reviews_total Total number of review assignments';
        $lines[] = '# TYPE reviews_total gauge';
        $lines[] = sprintf('reviews_total %d', count($reviews->findAll()));

        $lines[] = '# HELP reviews_submitted_total Number of submitted (completed) reviews';
        $lines[] = '# TYPE reviews_submitted_total gauge';
        $submitted = count(array_filter($reviews->findAll(), fn ($r) => $r->isSubmitted()));
        $lines[] = sprintf('reviews_submitted_total %d', $submitted);

        $lines[] = '# HELP users_total Total number of registered users';
        $lines[] = '# TYPE users_total gauge';
        $lines[] = sprintf('users_total %d', count($users->findAll()));

        $lines[] = '# HELP app_up Whether the application is serving requests';
        $lines[] = '# TYPE app_up gauge';
        $lines[] = 'app_up 1';

        $response = new Response(implode("\n", $lines)."\n");
        $response->headers->set('Content-Type', 'text/plain; version=0.0.4');

        return $response;
    }
}
