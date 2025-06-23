<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Doctrine\Filter\ReviewStatusFilter;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\ReviewStateProcessor;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ReviewStatusEnum;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['review:read', 'review:user:read']],
            security: "is_granted('READ', object)"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['review:read', 'review:user:read']],
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            normalizationContext: ['groups' => ['review:read']],
            denormalizationContext: ['groups' => ['review:write']],
            security: "is_granted('ROLE_USER')",
            processor: ReviewStateProcessor::class
        ),
        new Put(
            normalizationContext: ['groups' => ['review:read']],
            denormalizationContext: ['groups' => ['review:write']],
            security: "is_granted('EDIT', object)"
        ),
        new Delete(
            security: "is_granted('DELETE', object)"
        )
    ],
    normalizationContext: ['groups' => ['review:read']],
    denormalizationContext: ['groups' => ['review:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['product' => 'exact'])]
#[ApiFilter(ReviewStatusFilter::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Le contenu ne doit pas être vide.")]
    #[Groups(['review:read', 'review:write'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La note ne doit pas être vide.")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }}.'
    )]
    #[Groups(['review:read', 'review:write'])]
    private ?int $rating = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "L'utilisateur ne doit pas être vide.")]
    #[Groups(['review:read', 'review:user:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank(message: "Le produit ne doit pas être vide.")]
    #[Groups(['review:read', 'review:write'])]
    private ?Product $product = null;

    #[ORM\Column(enumType: ReviewStatusEnum::class)]
    #[Assert\NotBlank(message: "Le status ne doit pas être vide.")]
    #[Groups(['review:read'])]
    private ?ReviewStatusEnum $status = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de publication ne doit pas être vide.")]
    #[Groups(['review:read'])]
    private ?\DateTimeInterface $datePublication = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getStatus(): ?ReviewStatusEnum
    {
        return $this->status;
    }

    public function getStatusString(): string
    {
        return $this->status->value;
    }

    public function setStatus(ReviewStatusEnum $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }


    public function setDatePublication(\DateTimeInterface $datePublication): static
    {
        $this->datePublication = $datePublication;
        return $this;
    }
}