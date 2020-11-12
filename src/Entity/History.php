<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Timestampable;
use App\Repository\HistoryRepository;

/**
 * @ORM\Entity(repositoryClass=HistoryRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class History
{
    use Timestampable;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $response_json = [];

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="histories")
     */
    private $site;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponseJson(): ?array
    {
        return $this->response_json;
    }

    public function setResponseJson(?array $response_json): self
    {
        $this->response_json = $response_json;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

}
