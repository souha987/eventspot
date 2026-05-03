<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evenement;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EvenementType;

final class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(EvenementRepository $repo): Response
    {
        $evenements = $repo->findBy([], ['dateDebut' => 'ASC'], 6);
 
        return $this->render('evenement/home.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/evenements', name: 'app_evenement_index')]
    public function index(EvenementRepository $repo): Response
    {
        return $this->render('evenement/index.html.twig', [
            'evenements' => $repo->findAll(),
        ]);
    }

    #[Route('/evenements/{id}', name: 'app_evenement_show')]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/evenements/nouveau', name: 'app_evenement_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès.');

            return $this->redirectToRoute('app_evenement_index');
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/evenements/{id}/modifier', name: 'app_evenement_edit')]
    public function edit(Evenement $evenement, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Événement modifié.');

            return $this->redirectToRoute('app_evenement_index');
        }

        return $this->render('evenement/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
        
    #[Route('/evenements/{id}/supprimer', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé.');
        }

        return $this->redirectToRoute('app_evenement_index');
    }
}
