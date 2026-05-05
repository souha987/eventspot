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
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext:   ['groups' => ['evenement:read']],
    denormalizationContext: ['groups' => ['evenement:write']],
)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['evenement:read'])]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    #[ORM\Column(length: 255)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $titre = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 30)]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $description = null;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?\DateTimeInterface $dateDebut = null;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?Lieu $lieu = null;

    #[Assert\Range(min: 1)]
    #[ORM\Column]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?int $capaciteMax = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?float $prix = null;

    #[Assert\Choice(['conference', 'atelier', 'meetup', 'formation', 'concert'])]
    #[ORM\Column(length: 30)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $categorie = null;

    #[Assert\Choice(['brouillon', 'publie', 'complet', 'annule'])]
    #[ORM\Column(length: 20)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $statut = null;

    #[ORM\Column]
    #[Groups(['evenement:read'])]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['evenement:read'])]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['evenement:read'])]
    private ?User $organisateur = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Inscription::class)]
    private Collection $inscriptions;

    #[ORM\ManyToMany(targetEntity: TagEvenement::class, inversedBy: 'evenements')]
    #[Groups(['evenement:read', 'evenement:write'])]
    private Collection $tags;

    public function __construct()
    {
        $this->tags         = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(\DateTimeInterface $dateFin): static { $this->dateFin = $dateFin; return $this; }

    public function getLieu(): ?Lieu { return $this->lieu; }
    public function setLieu(?Lieu $lieu): static { $this->lieu = $lieu; return $this; }

    public function getCapaciteMax(): ?int { return $this->capaciteMax; }
    public function setCapaciteMax(int $capaciteMax): static { $this->capaciteMax = $capaciteMax; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(?float $prix): static { $this->prix = $prix; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(string $categorie): static { $this->categorie = $categorie; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(\DateTimeImmutable $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function getImageName(): ?string { return $this->imageName; }
    public function setImageName(?string $imageName): static { $this->imageName = $imageName; return $this; }

    public function getOrganisateur(): ?User { return $this->organisateur; }
    public function setOrganisateur(?User $organisateur): static { $this->organisateur = $organisateur; return $this; }

    public function getInscriptions(): Collection { return $this->inscriptions; }

    public function getTags(): Collection { return $this->tags; }

    public function addTag(TagEvenement $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(TagEvenement $tag): static
    {
        $this->tags->removeElement($tag);
        return $this;
    }
}