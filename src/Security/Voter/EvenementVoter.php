<?php
namespace App\Security\Voter;

use App\Entity\Evenement;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EvenementVoter extends Voter
{
    const EDIT   = 'EVENEMENT_EDIT';
    const DELETE = 'EVENEMENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Evenement;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        // Admin peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) return true;

        // Seul l'organisateur peut modifier/supprimer son événement
        return $subject->getOrganisateur() === $user;
    }
}