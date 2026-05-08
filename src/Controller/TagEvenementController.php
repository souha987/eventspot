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

final class TagEvenementController extends AbstractController
{
    #[Route('/tags', name: 'app_tag_index')]
    public function index(TagEvenementRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('tag_evenement/index.html.twig', [
            'tags' => $repo->findAll(),
        ]);
    }

    #[Route('/tags/nouveau', name: 'app_tag_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = new TagEvenement();
        $form = $this->createForm(TagEvenementType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tag);
            $em->flush();
            $this->addFlash('success', 'Tag créé avec succès.');
            return $this->redirectToRoute('app_tag_index');
        }

        return $this->render('tag_evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tags/{id}/supprimer', name: 'app_tag_delete', methods: ['POST'])]
    public function delete(Request $request, TagEvenement $tag, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $em->remove($tag);
            $em->flush();
            $this->addFlash('success', 'Tag supprimé.');
        }

        return $this->redirectToRoute('app_tag_index');
    }
}