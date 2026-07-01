<?php

namespace App\Entity;

use App\Enum\ReviewDecision;
use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private Article $article;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private User $reviewer;

    #[ORM\Column(enumType: ReviewDecision::class)]
    private ReviewDecision $decision = ReviewDecision::PENDING;

    /**
     * Score from 1 (weak) to 10 (excellent), filled once the review is submitted.
     */
    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comments = null;

    #[ORM\Column]
    private \DateTimeImmutable $assignedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    public function __construct()
    {
        $this->assignedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getReviewer(): User
    {
        return $this->reviewer;
    }

    public function setReviewer(User $reviewer): static
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    public function getDecision(): ReviewDecision
    {
        return $this->decision;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function isSubmitted(): bool
    {
        return null !== $this->reviewedAt;
    }

    public function getAssignedAt(): \DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function submit(ReviewDecision $decision, int $score, string $comments): static
    {
        $this->decision = $decision;
        $this->score = max(1, min(10, $score));
        $this->comments = $comments;
        $this->reviewedAt = new \DateTimeImmutable();

        return $this;
    }
}
