<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['tag:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['tag:read']]
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent créer des tags",
            denormalizationContext: ['groups' => ['tag:write']]
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent modifier des tags",
            denormalizationContext: ['groups' => ['tag:write']]
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Seuls les administrateurs peuvent supprimer des tags"
        )
    ],
    normalizationContext: ['groups' => ['tag:read']],
    denormalizationContext: ['groups' => ['tag:write']]
)]
#[ORM\Entity]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tag:read', 'product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tag:read', 'product:read', 'tag:write'])]
    #[Assert\NotBlank(message: "Le champ nom ne doit pas être vide.")]
    private ?string $name = null;

    #[Groups(['tag:read'])]
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'tags')]
    #[Assert\NotBlank(message: "Le produit ne doit pas être vide.")]
    private Collection $products;

    #[ORM\Column(length: 7, nullable: true)]
    #[Groups(['tag:read', 'product:read', 'tag:write'])]
    #[Assert\NotBlank(message: "Le champ couleur ne doit pas être vide.")]
    #[Assert\Regex(
        pattern: '/^#([0-9A-F]{3}){1,2}$/i',
        message: 'La couleur doit être au format hexadécimal (#RRGGBB ou #RGB).'
    )]
    private ?string $color = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addTag($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            if ($product->getTags()->contains($this)) {
                $product->removeTag($this);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }
}