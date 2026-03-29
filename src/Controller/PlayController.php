<?php

namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Services\GameService;
use App\Enum\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlayController extends AbstractController
{
    #[Route('/init', name:'app_init')]
    public function init(GameService $gameService, EntityManagerInterface $entityManager, GameRepository $gameRepository)
    {
        // calling the service to setup the game
        $game = $gameService->initGame($entityManager);
        // the first player to play is the human player
        $players = $game->getPlayers();
        foreach ($players as $player) {
            if ($player->isHuman()) {
                $game->setIdPlayerNextTurn($player->getId());
                $entityManager->persist($game);
                $entityManager->flush();
                break;
            }
        }

        return $this->redirectToRoute('app_play');
    }

    #[Route('/play', name: 'app_play')]
    public function index(GameRepository $gameRepository, CardRepository $cardRepository, PlayerRepository $playerRepository): Response
    {
        $currentGame = $gameRepository->findOneBy(['status' => Status::IN_PROGRESS]);
        // if no game in progress -> error message
        if (!$currentGame) {
            throw $this->createNotFoundException('Aucune partie en cours trouvée.');
        }
        $cards = $cardRepository->findBy(['game'=>$currentGame->getId()]);
        $players = $playerRepository->findBy(['game'=>$currentGame->getId()]);
        $humanPlayer = $playerRepository->findOneBy(['game' =>$currentGame->getId(), 'isHuman'=>true]);
        return $this->render('play/index.html.twig', ['game' => $currentGame, 'cards' => $cards, 'players' => $players, 'humanPlayer' => $humanPlayer]);
    }

    #[Route('/win', name:'app_win')]
    public function win(Request $request, PlayerRepository $playerRepository): Response
    {
        $winnerId = $request->query->get('winner');
        $winner = $playerRepository->findOneBy(['id' => $winnerId]);
        if($winner->isHuman()) {
            return $this->render('/play/win.html.twig', ['winner' => $winner]);
        } else {
            return $this->render('/play/loose.html.twig', ['winner' => $winner]);
        }
        
    }
}
