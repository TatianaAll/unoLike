<?php

namespace App\Controller;

use App\Services\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlayController extends AbstractController
{
    #[Route('/init', name:'app_init')]
    public function init(GameService $gameService, EntityManagerInterface $entityManager)
    {
        // calling the service to setup the game
        $gameService->initGame($entityManager);
        return $this->redirectToRoute('app_play');
    }

    #[Route('/play', name: 'app_play')]
    public function index(): Response
    {
        return $this->render('play/index.html.twig', [
            'controller_name' => 'PlayController',
        ]);
    }
}
