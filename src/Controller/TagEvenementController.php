<?php

namespace App\Controller;

use App\Entity\TagEvenement;
use App\Form\TagEvenementType;
use App\Repository\TagEvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tags')]
class TagEvenementController extends AbstractController
{
    #[Route('/', name: 'app_tag_liste', methods: ['GET'])]
    public function liste(TagEvenementRepository $repo): Response
    {
        return $this->render('tag/liste.html.twig', ['tags' => $repo->findAll()]);
    }

    #[Route('/nouveau', name: 'app_tag_nouveau', methods: ['GET', 'POST'])]
    public function nouveau(Request $request, EntityManagerInterface $em): Response
    {
        $tag = new TagEvenement();
        $form = $this->createForm(TagEvenementType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tag);
            $em->flush();
            $this->addFlash('success', '🏷️ Tag créé avec succès !');
            return $this->redirectToRoute('app_tag_liste');
        }

        return $this->render('tag/form.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/supprimer', name: 'app_tag_supprimer', methods: ['POST'])]
    public function supprimer(TagEvenement $tag, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tag->getId(), $request->request->get('_token'))) {
            $em->remove($tag);
            $em->flush();
            $this->addFlash('danger', '🗑️ Tag supprimé.');
        }
        return $this->redirectToRoute('app_tag_liste');
    }
}