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

final class LieuController extends AbstractController
{
    #[Route('/lieux', name: 'app_lieu_index')]
    public function index(LieuRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANISATEUR');

        return $this->render('lieu/index.html.twig', [
            'lieux' => $repo->findAll(),
        ]);
    }

    #[Route('/lieux/nouveau', name: 'app_lieu_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', 'Lieu créé avec succès.');
            return $this->redirectToRoute('app_lieu_index');
        }

        return $this->render('lieu/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}