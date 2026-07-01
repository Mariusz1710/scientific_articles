<?php

namespace App\Controller;

use App\Enum\ArticleStatus;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function search(Request $request, ArticleRepository $articles, CategoryRepository $categories): Response
    {
        $criteria = [
            'title' => $request->query->get('title'),
            'authors' => $request->query->get('authors'),
            'keywords' => $request->query->get('keywords'),
            'status' => $request->query->get('status'),
            'category' => $request->query->get('category'),
        ];

        return $this->render('article/search.html.twig', [
            'articles' => array_filter($criteria) ? $articles->search($criteria) : [],
            'categories' => $categories->findAll(),
            'statuses' => ArticleStatus::cases(),
            'criteria' => $criteria,
        ]);
    }
}
