<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Game;
use App\Entity\Player;
use App\Repository\CardRepository;
use App\Repository\PlayerRepository;
use App\Services\GameService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClickController extends AbstractController
{
  #[Route('/click/{cardId}', name: 'app_click', requirements: ['cardId'=>'\d+'], methods: ['POST'])]
  public function playCard(int $cardId, CardRepository $cardRepository, EntityManagerInterface $entityManager): Response
  {
    // if a card is played i wanna to change the player_id to null and update the updated at in Game
    $cardPlayed = $cardRepository->findOneBy(['id' => $cardId]);
    // can't play 2 times the ssame card
    if ($cardPlayed->getPlayer() === null) {
      return $this->redirectToRoute('app_play');
    }
    
    $gameInProgress = $cardPlayed->getGame();
    $turn = $gameInProgress->getTurn();
    // security : if not the player turn return without other action
    if ($gameInProgress->getTurn() !== 0) {
      return $this->redirectToRoute('app_play');
    }

    if ($this->canPlayCard($cardPlayed, $gameInProgress)) {
      // the current card is discard so the player = null;
      $cardPlayed->setPlayer(null);

      // Setting the new color and value played
      $gameInProgress->setCurrentColor($cardPlayed->getColor());
      $gameInProgress->setCurrentValue($cardPlayed->getLabel());
      // implementing the turn
      $this->implementTurn($gameInProgress, $turn);

      // Setting the updated at at the current time
      $gameInProgress->setUpdatedAt(new DateTime());

      $entityManager->persist($gameInProgress);
      $entityManager->persist($cardPlayed);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_play');
  }

  #[Route('/enemy/{playerId}', name:'app_enemy', requirements: ['playerId'=>'\d+'], methods: ['GET'])]
  public function botTurn(int $playerId, PlayerRepository $playerRepository, CardRepository $cardRepository, EntityManagerInterface $entityManager, GameService $gameService) 
  {
    // 1- find the player
    $currentPlayer = $playerRepository->findOneBy(['id' => $playerId]);
    // if no player or human player error
    if(!isset($currentPlayer) || $currentPlayer->isHuman()) {
      return $this->redirectToRoute('app_play');
    }

    // get the game
    $game = $currentPlayer->getGame();
    $turn = $game->getTurn();
    // get the cards of the player
    $cards = $cardRepository->findBy(['player' => $currentPlayer, 'game' => $game]);
    // the player haven't play yet so played set at false
    $played = false;
    // parsing all the card to see if the enemy can play
    foreach ($cards as $card) {
      // call a private function to verify if the card is playable
      if ($this->canPlayCard($card, $game) && !$played) {
        $card->setPlayer(null);
        $game->setCurrentColor($card->getColor());
        $game->setCurrentValue($card->getLabel());
        $currentPlayer->setCardsInHand($currentPlayer->getCardsInHand() - 1);
        $played = true;
        break;
      }
    }
    if (!$played) {
      $gameService->getRandomCards(1, $game, $entityManager, $currentPlayer);
    }
    $this->implementTurn($game, $turn);
    $game->setUpdatedAt(new DateTime());
    $entityManager->persist($game);
    $entityManager->persist($currentPlayer);
    $entityManager->flush();
    return $this->redirectToRoute('app_play');
  }
  
  #[Route('/draw/{playerId}', name:'app_draw_card', requirements: ['playerId'=>'\d+'], methods: ['GET'])]
  public function playerDrawCard(int $playerId, PlayerRepository $playerRepository, GameService $gameService, EntityManagerInterface $entityManager) {
    // 1- find the player
    $currentPlayer = $playerRepository->findOneBy(['id' => $playerId]);
    // if no player or not human  -> error
    if(!isset($currentPlayer) || !($currentPlayer->isHuman())) {
      return $this->redirectToRoute('app_play');
    }
    // get the game
    $game = $currentPlayer->getGame();
    $turn = $game->getTurn();
    // metho draw
    $gameService->getRandomCards(1, $game, $entityManager, $currentPlayer);
    $this->implementTurn($game, $turn);
    $game->setUpdatedAt(new DateTime());
    $entityManager->persist($game);
    $entityManager->persist($currentPlayer);
    $entityManager->flush();
    return $this->redirectToRoute('app_play');
  }

  private function implementTurn(Game $game, int $turn): Game
  {
    $nextTurn = ($turn + 1) % 4;
    return $game->setTurn($nextTurn);
  }

  private function canPlayCard(Card $card, Game $game): bool
  {
    return $card->getColor() === $game->getCurrentColor() || $card->getLabel() === $game->getCurrentValue();
  }

  private function specialCardsEffect(Card $card) {
    if($card->getLabel() == "X" || $card->getLabel() == "S" || $card->getLabel() == "+2" ) {
      if($card->getLabel() == "X") {
        $currentGame = $card->getGame();
        return $currentGame->setTurn((($currentGame->getTurn()) + 1) % 4);
      } else {
        return;
      }
    }
  }
}
