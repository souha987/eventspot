<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\TagEvenementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagEvenementRepository::class)]
class TagEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 50, unique: true)]
    private ?string $nom = null;

    #[Assert\Regex('/^#[0-9A-Fa-f]{6}$/')]
    #[ORM\Column(length: 7)]
    private ?string $couleur = null;

    #[ORM\ManyToMany(mappedBy: 'tags', targetEntity: Evenement::class)]
    private Collection $evenements;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }
}
