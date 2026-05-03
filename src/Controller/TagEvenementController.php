<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TagEvenementController extends AbstractController
{
    #[Route('/tag/evenement', name: 'app_tag_evenement')]
    public function index(): Response
    {
        return $this->render('tag_evenement/index.html.twig', [
            'controller_name' => 'TagEvenementController',
        ]);
    }
}
