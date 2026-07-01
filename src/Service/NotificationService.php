<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persists in-app notifications for users (visible on their dashboard).
 *
 * Extension point: to also send e-mails, inject Symfony Mailer here and
 * dispatch a message in notify(); MAILER_DSN is already wired in .env.
 */
class NotificationService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function notify(User $recipient, string $message, ?Article $relatedArticle = null): Notification
    {
        $notification = new Notification();
        $notification->setRecipient($recipient);
        $notification->setMessage($message);
        $notification->setRelatedArticle($relatedArticle);

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    public function notifyArticleStatusChanged(Article $article): void
    {
        $this->notify(
            $article->getSubmitter(),
            sprintf('Status artykułu "%s" zmienił się na: %s.', $article->getTitle(), $article->getStatus()->label()),
            $article
        );
    }
}
