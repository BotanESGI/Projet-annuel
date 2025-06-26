<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class OrderAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La rue ne doit pas être vide.")]
    private ?string $shippingStreet = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La ville ne doit pas être vide.")]
    private ?string $shippingCity = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le code postal ne doit pas être vide.")]
    #[Assert\Regex(
        pattern: '/^\d{5}(-\d{4})?$/',
        message: "Le code postal doit être valide (format: 12345 ou 12345-6789)."
    )]
    private ?string $shippingPostalCode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippingStreet(): ?string
    {
        return $this->shippingStreet;
    }

    public function setShippingStreet(string $shippingStreet): static
    {
        $this->shippingStreet = $shippingStreet;
        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(string $shippingCity): static
    {
        $this->shippingCity = $shippingCity;
        return $this;
    }

    public function getShippingPostalCode(): ?string
    {
        return $this->shippingPostalCode;
    }

    public function setShippingPostalCode(string $shippingPostalCode): static
    {
        $this->shippingPostalCode = $shippingPostalCode;
        return $this;
    }
}