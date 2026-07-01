<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\CsvExportService;
use App\Service\PdfExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/export')]
#[IsGranted('ROLE_EDITOR')]
class ExportController extends AbstractController
{
    #[Route('/csv', name: 'app_export_csv', methods: ['GET'])]
    public function csv(Request $request, ArticleRepository $articles, CsvExportService $csv): Response
    {
        $criteria = array_filter([
            'title' => $request->query->get('title'),
            'status' => $request->query->get('status'),
            'category' => $request->query->get('category'),
        ]);

        $list = $criteria ? $articles->search($criteria) : $articles->findAll();

        return $csv->export($list);
    }

    #[Route('/pdf', name: 'app_export_pdf', methods: ['GET'])]
    public function pdf(Request $request, ArticleRepository $articles, PdfExportService $pdf): Response
    {
        $criteria = array_filter([
            'title' => $request->query->get('title'),
            'status' => $request->query->get('status'),
            'category' => $request->query->get('category'),
        ]);

        $list = $criteria ? $articles->search($criteria) : $articles->findAll();

        return $pdf->renderArticleList($list);
    }
}
