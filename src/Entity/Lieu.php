<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\LieuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext:   ['groups' => ['lieu:read']],
    denormalizationContext: ['groups' => ['lieu:write']],
)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['lieu:read', 'evenement:read'])]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['lieu:read', 'lieu:write', 'evenement:read'])]
    private ?string $nom = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    #[Groups(['lieu:read', 'lieu:write'])]
    private ?string $adresse = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    #[Groups(['lieu:read', 'lieu:write', 'evenement:read'])]
    private ?string $ville = null;

    #[Assert\Range(min: 1)]
    #[ORM\Column]
    #[Groups(['lieu:read', 'lieu:write'])]
    private ?int $capacite = null;

    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'lieu')]
    private Collection $evenements;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(string $adresse): static { $this->adresse = $adresse; return $this; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(string $ville): static { $this->ville = $ville; return $this; }

    public function getCapacite(): ?int { return $this->capacite; }
    public function setCapacite(int $capacite): static { $this->capacite = $capacite; return $this; }

    public function getEvenements(): Collection { return $this->evenements; }

    public function addEvenement(Evenement $evenement): static
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setLieu($this);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): static
    {
        if ($this->evenements->removeElement($evenement)) {
            if ($evenement->getLieu() === $this) {
                $evenement->setLieu(null);
            }
        }
        return $this;
    }
}