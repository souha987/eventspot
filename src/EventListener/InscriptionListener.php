<?php
namespace App\EventListener;

use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

#[AsDoctrineListener(event: Events::postPersist)]
class InscriptionListener
{
    public function __construct(private MailerInterface $mailer) {}

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof Inscription) {
            return;
        }

        $user      = $object->getParticipant();
        $evenement = $object->getEvenement();

        $email = (new TemplatedEmail())
            ->from('noreply@eventspot.fr')
            ->to($user->getEmail())
            ->subject('Confirmation d\'inscription — ' . $evenement->getTitre())
            ->htmlTemplate('emails/inscription_confirmation.html.twig')
            ->context([
                'user'      => $user,
                'evenement' => $evenement,
            ]);

        $this->mailer->send($email);
    }
}