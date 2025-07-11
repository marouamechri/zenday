<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['tag:read']],
    denormalizationContext: ['groups' => ['tag:write']],
    order: ['name' => 'ASC']
)]

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le nom du tag ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Le nom du tag doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom du tag ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['tag:read', 'tag:write', 'moment:read', 'moment:write'])]
    #[ORM\Column(length: 100, nullable: false)]
    private ?string $name;

    /**
     * @var Collection<int, Moment>
     */
    #[ORM\ManyToMany(targetEntity: Moment::class, mappedBy: 'tags')]
    private Collection $moments;

    public function __construct()
    {
        $this->moments = new ArrayCollection();
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

    /**
     * @return Collection<int, Moment>
     */
    public function getMoments(): Collection
    {
        return $this->moments;
    }

    public function addMoment(Moment $moment): static
    {
        if (!$this->moments->contains($moment)) {
            $this->moments->add($moment);
            $moment->addTag($this);
        }

        return $this;
    }

    public function removeMoment(Moment $moment): static
    {
        if ($this->moments->removeElement($moment)) {
            $moment->removeTag($this);
        }

        return $this;
    }
}
