<?php

namespace App\Entity;

use App\Repository\TypesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypesRepository::class)
 */
class Types
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recommendedVersion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $minimumVersion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRecommendedVersion(): ?string
    {
        return $this->recommendedVersion;
    }

    public function setRecommendedVersion(string $recommendedVersion): self
    {
        $this->recommendedVersion = $recommendedVersion;

        return $this;
    }

    public function getMinimumVersion(): ?string
    {
        return $this->minimumVersion;
    }

    public function setMinimumVersion(?string $minimumVersion): self
    {
        $this->minimumVersion = $minimumVersion;

        return $this;
    }
}
