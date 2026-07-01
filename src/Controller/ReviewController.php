<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Review;
use App\Entity\User;
use App\Enum\ArticleStatus;
use App\Enum\ReviewDecision;
use App\Form\ReviewFormType;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewController extends AbstractController
{
    #[Route('/reviews', name: 'app_review_index', methods: ['GET'])]
    #[IsGranted('ROLE_REVIEWER')]
    public function index(ReviewRepository $reviews): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('review/index.html.twig', [
            'reviews' => $reviews->findForReviewer($user),
        ]);
    }

    #[Route('/reviews/{id}', name: 'app_review_show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REVIEWER')]
    public function show(Request $request, Review $review, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        if ($review->getReviewer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($review->isSubmitted()) {
            return $this->render('review/show.html.twig', ['review' => $review, 'form' => null]);
        }

        $form = $this->createForm(ReviewFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $review->submit(
                ReviewDecision::from($data['decision']),
                (int) $data['score'],
                $data['comments']
            );

            $article = $review->getArticle();
            $article->setStatus(ArticleStatus::IN_REVIEW);
            $this->recalculateArticleStatusFromReviews($article);

            $em->flush();
            $notifications->notifyArticleStatusChanged($article);

            $this->addFlash('success', 'Recenzja została zapisana.');

            return $this->redirectToRoute('app_review_index');
        }

        return $this->render('review/show.html.twig', ['review' => $review, 'form' => $form]);
    }

    #[Route('/articles/{id}/assign-reviewer', name: 'app_article_assign_reviewer', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function assignReviewer(Request $request, Article $article, UserRepository $users, EntityManagerInterface $em): Response
    {
        $reviewerId = $request->request->get('reviewer');

        if ($request->isMethod('POST') && $reviewerId) {
            $reviewer = $users->find($reviewerId);

            if ($reviewer) {
                $review = new Review();
                $review->setReviewer($reviewer);
                $article->addReview($review);

                if (ArticleStatus::SUBMITTED === $article->getStatus()) {
                    $article->setStatus(ArticleStatus::IN_REVIEW);
                }

                $em->flush();
                $this->addFlash('success', 'Recenzent przypisany.');
            }

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('review/assign.html.twig', [
            'article' => $article,
            'reviewers' => $users->findReviewers(),
        ]);
    }

    /**
     * Once every assigned review has been submitted, derives the article's
     * overall status from the individual reviewer decisions: rejected if any
     * reviewer rejects it, accepted only if every reviewer accepted (or asked
     * for at most minor revisions).
     */
    private function recalculateArticleStatusFromReviews(Article $article): void
    {
        $reviews = $article->getReviews();

        if ($reviews->isEmpty()) {
            return;
        }

        foreach ($reviews as $review) {
            if (!$review->isSubmitted()) {
                return; // still waiting on other reviewers
            }
        }

        $hasRejection = false;
        $hasMajorRevision = false;

        foreach ($reviews as $review) {
            if (ReviewDecision::REJECT === $review->getDecision()) {
                $hasRejection = true;
            }
            if (ReviewDecision::MAJOR_REVISION === $review->getDecision()) {
                $hasMajorRevision = true;
            }
        }

        if ($hasRejection) {
            $article->setStatus(ArticleStatus::REJECTED);
        } elseif (!$hasMajorRevision) {
            $article->setStatus(ArticleStatus::ACCEPTED);
        }
        // otherwise it stays IN_REVIEW pending a revised version from the author
    }
}
