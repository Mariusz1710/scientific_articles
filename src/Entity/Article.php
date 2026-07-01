<?php

namespace App\Entity;

use App\Enum\ArticleFileFormat;
use App\Enum\ArticleStatus;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    /**
     * Free-text list of authors (e.g. "Jan Kowalski, Anna Nowak"), independent
     * from the submitting account so co-authors without accounts can be listed.
     */
    #[ORM\Column(length: 500)]
    private string $authors;

    #[ORM\Column(type: 'text')]
    private string $abstract;

    /**
     * Comma separated keywords.
     */
    #[ORM\Column(length: 500)]
    private string $keywords;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'submittedArticles')]
    #[ORM\JoinColumn(nullable: false)]
    private User $submitter;

    #[ORM\Column(enumType: ArticleStatus::class)]
    private ArticleStatus $status = ArticleStatus::DRAFT;

    #[ORM\Column(nullable: true)]
    private ?string $currentFilePath = null;

    #[ORM\Column(enumType: ArticleFileFormat::class, nullable: true)]
    private ?ArticleFileFormat $currentFileFormat = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    /** @var Collection<int, ArticleVersion> */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleVersion::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['uploadedAt' => 'DESC'])]
    private Collection $versions;

    /** @var Collection<int, Review> */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Review::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->versions = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthors(): string
    {
        return $this->authors;
    }

    public function setAuthors(string $authors): static
    {
        $this->authors = $authors;

        return $this;
    }

    public function getAbstract(): string
    {
        return $this->abstract;
    }

    public function setAbstract(string $abstract): static
    {
        $this->abstract = $abstract;

        return $this;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    /** @return list<string> */
    public function getKeywordsList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->keywords))));
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSubmitter(): User
    {
        return $this->submitter;
    }

    public function setSubmitter(User $submitter): static
    {
        $this->submitter = $submitter;

        return $this;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function setStatus(ArticleStatus $status): static
    {
        $this->status = $status;
        $this->touch();

        return $this;
    }

    public function getCurrentFilePath(): ?string
    {
        return $this->currentFilePath;
    }

    public function setCurrentFilePath(?string $currentFilePath): static
    {
        $this->currentFilePath = $currentFilePath;

        return $this;
    }

    public function getCurrentFileFormat(): ?ArticleFileFormat
    {
        return $this->currentFileFormat;
    }

    public function setCurrentFileFormat(?ArticleFileFormat $currentFileFormat): static
    {
        $this->currentFileFormat = $currentFileFormat;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function markSubmitted(): static
    {
        $this->submittedAt = new \DateTimeImmutable();
        $this->status = ArticleStatus::SUBMITTED;
        $this->touch();

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function markPublished(): static
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->status = ArticleStatus::PUBLISHED;
        $this->touch();

        return $this;
    }

    /** @return Collection<int, ArticleVersion> */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(ArticleVersion $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setArticle($this);
        }

        return $this;
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setArticle($this);
        }

        return $this;
    }

    public function isEditable(): bool
    {
        return ArticleStatus::DRAFT === $this->status;
    }
}
