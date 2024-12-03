<?php

namespace App\Entity;

use App\Repository\POIRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: POIRepository::class)]
class POI
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['poi'])]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['poi'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['poi'])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Groups(['poi'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['poi'])]
    #[ORM\Column]
    private ?float $latitude = null;

    #[Groups(['poi'])]
    #[ORM\Column]
    private ?float $longitude = null;

    #[Groups(['poi'])]
    #[ORM\Column]
    private ?int $altitude = null;

    #[Groups(['poi'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[Groups(['poi'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    public function setAltitude(int $altitude): static
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
