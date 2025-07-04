<?php

namespace App\Entity;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN')", normalizationContext: ['groups' => ['address:read']]),
        new GetCollection(security: "is_granted('ROLE_ADMIN')", normalizationContext: ['groups' => ['address:read']]),
        new Post(security: "is_granted('ROLE_ADMIN')", denormalizationContext: ['groups' => ['address:write']]),
        new Put(security: "is_granted('ROLE_ADMIN')", denormalizationContext: ['groups' => ['address:write']]),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
#[ORM\Entity]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['address:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La rue ne doit pas être vide.")]
    #[Groups(['address:read', 'address:write'])]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ville ne doit pas être vide.")]
    #[Groups(['address:read', 'address:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le code postal ne doit pas être vide.")]
    #[Assert\Regex(
        pattern: '/^\d{5}(-\d{4})?$/',
        message: "Le code postal doit être valide (format: 12345 ou 12345-6789)."
    )]
    #[Groups(['address:read', 'address:write'])]
    private ?string $postalCode = null;

    // ...
    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['address:read', 'address:write'])]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['address:read', 'address:write'])]
    private bool $isDefault = false;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: ['shipping', 'billing'], message: "Le type doit être 'shipping' ou 'billing'.")]
    #[Groups(['address:read', 'address:write'])]
    private ?string $type = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['address:read', 'address:write'])]
    private bool $isDefaultBilling = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
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

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isDefaultBilling(): bool
    {
        return $this->isDefaultBilling;
    }

    public function setIsDefaultBilling(bool $isDefaultBilling): self
    {
        $this->isDefaultBilling = $isDefaultBilling;
        return $this;
    }
}