<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieux')]
class LieuController extends AbstractController
{
    #[Route('/', name: 'app_lieu_liste', methods: ['GET'])]
    public function liste(LieuRepository $repo): Response
    {
        return $this->render('lieu/liste.html.twig', ['lieux' => $repo->findAll()]);
    }

    #[Route('/nouveau', name: 'app_lieu_nouveau', methods: ['GET', 'POST'])]
    public function nouveau(Request $request, EntityManagerInterface $em): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', '📍 Lieu ajouté avec succès !');
            return $this->redirectToRoute('app_lieu_liste');
        }

        return $this->render('lieu/form.html.twig', ['form' => $form]);
    }
}