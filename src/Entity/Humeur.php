<?php

namespace App\Entity;

use App\Repository\HumeurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(uriTemplate: '/humeur'), 
        new Post(
            uriTemplate: '/humeur',
            security: "is_granted('ROLE_ADMIN')",
            validationContext: ['groups' => ['humeur:write']]
        ),
        new Put(
            uriTemplate: '/humeur/{id}',
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Delete(
            uriTemplate: '/humeur/{id}',
            security: "is_granted('ROLE_ADMIN')"
        )
    ],
    routePrefix: '/api', // Préfixe toutes les routes avec /api
    normalizationContext: ['groups' => ['humeur:read']],
    denormalizationContext: ['groups' => ['humeur:write']],
    order: ['name' => 'ASC']
)]
#[ORM\Entity(repositoryClass: HumeurRepository::class)]
class Humeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[Assert\NotBlank(message: "Le nom du humeur ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Le nom du humeur doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom du humeur ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['humeur:read', 'humeur:write', 'moment:read'])] // Correction des groupes
    #[ORM\Column(length: 100, nullable: false)]
    private ?string $name;

    /**
     * @var Collection<int, Moment>
     */
    #[ORM\OneToMany(targetEntity: Moment::class, mappedBy: 'humeur')]
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
            $moment->setHumeur($this);
        }

        return $this;
    }

    public function removeMoment(Moment $moment): static
    {
        if ($this->moments->removeElement($moment)) {
            // set the owning side to null (unless already changed)
            if ($moment->getHumeur() === $this) {
                $moment->setHumeur(null);
            }
        }

        return $this;
    }
}
