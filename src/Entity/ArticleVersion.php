<?php

namespace App\Entity;

use App\Enum\ArticleFileFormat;
use App\Repository\ArticleVersionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Snapshot of a file uploaded for an Article — kept so authors can see the
 * history of what they submitted/edited before the final submission to review.
 */
#[ORM\Entity(repositoryClass: ArticleVersionRepository::class)]
class ArticleVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private Article $article;

    #[ORM\Column]
    private string $filePath;

    #[ORM\Column(length: 191)]
    private string $originalFileName;

    #[ORM\Column(enumType: ArticleFileFormat::class)]
    private ArticleFileFormat $format;

    #[ORM\Column]
    private \DateTimeImmutable $uploadedAt;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
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

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): static
    {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    public function getFormat(): ArticleFileFormat
    {
        return $this->format;
    }

    public function setFormat(ArticleFileFormat $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getUploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }
}
