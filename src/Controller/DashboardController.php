<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function index(NotificationRepository $notifications): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('dashboard.html.twig', [
            'notifications' => $notifications->findForUser($user),
        ]);
    }

    #[Route('/notifications/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function markRead(int $id, NotificationRepository $notifications, \Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $notification = $notifications->find($id);

        if ($notification && $notification->getRecipient() === $this->getUser()) {
            $notification->markRead();
            $em->flush();
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
