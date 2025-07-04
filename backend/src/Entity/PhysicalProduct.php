<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'PhysicalProduct',
    normalizationContext: ['groups' => ['product:read', 'physical_product:read']],
    denormalizationContext: ['groups' => ['product:write', 'physical_product:write']]
)]
#[ORM\Entity]
class PhysicalProduct extends Product
{
    #[ORM\Column(type: "json", nullable: true)]
    #[Groups(['product:read', 'physical_product:read', 'physical_product:write'])]
    private ?array $characteristics = null;

    public function getCharacteristics(): ?array
    {
        return $this->characteristics;
    }

    public function setCharacteristics($characteristics): static
    {
        if (is_string($characteristics)) {
            $decoded = json_decode($characteristics, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->characteristics = $decoded;
            } else {
                $this->characteristics = [];
            }
        } elseif (is_array($characteristics)) {
            $this->characteristics = $characteristics;
        } else {
            $this->characteristics = [];
        }

        return $this;
    }
}