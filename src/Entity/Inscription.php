<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\InscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateInscription = null;

    #[Assert\Choice(['confirmee','en_attente','annulee'])]
    #[ORM\Column(length: 15)]
    private ?string $statut = null;

    #[Assert\Length(max:500)]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $participant = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $evenement = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateInscription(): ?\DateTimeImmutable { return $this->dateInscription; }
    public function setDateInscription(\DateTimeImmutable $dateInscription): static { $this->dateInscription = $dateInscription; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }

    public function getParticipant(): ?User { return $this->participant; }
    public function setParticipant(?User $participant): static { $this->participant = $participant; return $this; }

    public function getEvenement(): ?Evenement { return $this->evenement; }
    public function setEvenement(?Evenement $evenement): static { $this->evenement = $evenement; return $this; }
}