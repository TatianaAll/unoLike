<?php

namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Services\GameService;
use App\Enum\Status;
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
    public function index(GameRepository $gameRepository, CardRepository $cardRepository, PlayerRepository $playerRepository): Response
    {
        $currentGame = $gameRepository->findOneBy(['status' => Status::IN_PROGRESS]);
        if (!$currentGame) {
            throw $this->createNotFoundException('Aucune partie en cours trouvée.');
        }
        $cards = $cardRepository->findBy(['game'=>$currentGame->getId()]);
        $players = $playerRepository->findBy(['game'=>$currentGame->getId()]);
        return $this->render('play/index.html.twig', ['game' => $currentGame, 'cards' => $cards, 'players' => $players]);
    }
}
