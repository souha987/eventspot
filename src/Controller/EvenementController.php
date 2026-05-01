<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Form\EvenementType;
use App\Form\InscriptionType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/evenements')]
class EvenementController extends AbstractController
{
    // ─── Accueil : 6 prochains événements ───────────────────────────────────
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(EvenementRepository $repo): Response
    {
        $evenements = $repo->findProchains(6);
        return $this->render('evenement/index.html.twig', ['evenements' => $evenements]);
    }

    // ─── Liste complète ──────────────────────────────────────────────────────
    #[Route('/liste', name: 'app_evenement_liste', methods: ['GET'])]
    public function liste(EvenementRepository $repo): Response
    {
        $evenements = $repo->findAll();
        return $this->render('evenement/liste.html.twig', ['evenements' => $evenements]);
    }

    // ─── Détail ──────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'app_evenement_detail', methods: ['GET'])]
    public function detail(Evenement $evenement): Response
    {
        $inscrits = $evenement->getInscriptions()->count();
        $taux = $evenement->getCapaciteMax() > 0
            ? round(($inscrits / $evenement->getCapaciteMax()) * 100)
            : 0;

        return $this->render('evenement/detail.html.twig', [
            'evenement' => $evenement,
            'inscrits'  => $inscrits,
            'taux'      => $taux,
        ]);
    }

    // ─── Créer ───────────────────────────────────────────────────────────────
    #[Route('/nouveau', name: 'app_evenement_nouveau', methods: ['GET', 'POST'])]
    public function nouveau(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImage($form, $evenement, $slugger);
            $evenement->setDateCreation(new \DateTimeImmutable());
            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', '✅ Événement créé avec succès !');
            return $this->redirectToRoute('app_evenement_liste');
        }

        return $this->render('evenement/form.html.twig', [
            'form'   => $form,
            'titre'  => 'Créer un événement',
            'action' => 'Créer',
        ]);
    }

    // ─── Modifier ────────────────────────────────────────────────────────────
    #[Route('/{id}/modifier', name: 'app_evenement_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImage($form, $evenement, $slugger);
            $em->flush();
            $this->addFlash('success', '✏️ Événement modifié avec succès !');
            return $this->redirectToRoute('app_evenement_detail', ['id' => $evenement->getId()]);
        }

        return $this->render('evenement/form.html.twig', [
            'form'      => $form,
            'titre'     => 'Modifier l\'événement',
            'action'    => 'Modifier',
            'evenement' => $evenement,
        ]);
    }

    // ─── Supprimer ───────────────────────────────────────────────────────────
    #[Route('/{id}/supprimer', name: 'app_evenement_supprimer', methods: ['POST'])]
    public function supprimer(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('danger', '🗑️ Événement supprimé.');
        }
        return $this->redirectToRoute('app_evenement_liste');
    }

    // ─── S'inscrire ──────────────────────────────────────────────────────────
    #[Route('/{id}/inscription', name: 'app_evenement_inscription', methods: ['GET', 'POST'])]
    public function inscription(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $inscription = new Inscription();
        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->setEvenement($evenement);
            $inscription->setDateInscription(new \DateTime());
            $inscription->setStatut('en_attente');
            $em->persist($inscription);
            $em->flush();
            $this->addFlash('success', '🎉 Inscription enregistrée !');
            return $this->redirectToRoute('app_evenement_detail', ['id' => $evenement->getId()]);
        }

        return $this->render('evenement/inscription.html.twig', [
            'form'      => $form,
            'evenement' => $evenement,
        ]);
    }

    // ─── Helper upload image ─────────────────────────────────────────────────
    private function handleImage($form, Evenement $evenement, SluggerInterface $slugger): void
    {
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('images_directory'), $newFilename);
            $evenement->setImageName($newFilename);
        }
    }
}