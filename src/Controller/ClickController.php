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

    $currentPlayer = $cardPlayed->getPlayer();
    if ($this->canPlayCard($cardPlayed, $gameInProgress)) {
      // Coutn the cards in hand -1
      $currentPlayer->setCardsInHand($currentPlayer->getCardsInHand() - 1);
        if($currentPlayer->getCardsInHand() == 0) {
          return $this->redirectToRoute('app_win', ['winner' => $currentPlayer->getId()]);
        }
      // the current card is discard so the player = null;
      $cardPlayed->setPlayer(null);

      // Setting the new color and value played
      $gameInProgress->setCurrentColor($cardPlayed->getColor());
      $gameInProgress->setCurrentValue($cardPlayed->getLabel());
      // appliyng effect
      $this->specialCardsEffect($cardPlayed);
      $gameInProgress->setDirection(!$gameInProgress->isDirection());

      // implementing the turn
      $nextTurn = $this->implementTurn($gameInProgress, $turn);
      $nextPlayerId = $this->getNextPlayerId($currentPlayer, $gameInProgress, $nextTurn);
      $gameInProgress->setIdPlayerNextTurn($nextPlayerId);

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
    $humanPlayer = $playerRepository->findOneBy(['isHuman' => true, 'game' => $currentPlayer->getGame()->getId()]);
    // if no player or human player error
    dump("test", $currentPlayer);
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
        // number of cards in hand -1
        $currentPlayer->setCardsInHand($currentPlayer->getCardsInHand() - 1);
        if($currentPlayer->getCardsInHand() == 0) {
          return $this->redirectToRoute('app_win', ['winner' => $currentPlayer->getId()]);
        }
        // discard the card
        $card->setPlayer(null);
        // change the game color and value
        $game->setCurrentColor($card->getColor());
        $game->setCurrentValue($card->getLabel());
        // appliyng the card effect
        $this->specialCardsEffect($card);
        $played = true;
        break;
      }
    }
    if (!$played) {
      $gameService->getRandomCards(1, $game, $entityManager, $currentPlayer);
    }
    $nextTurn = $this->implementTurn($game, $turn);

    $nextPlayerId = $this->getNextPlayerId($humanPlayer, $game, $nextTurn);
    // dd($nextPlayerId);
    $game->setIdPlayerNextTurn($nextPlayerId);
    $game->setUpdatedAt(new DateTime());
    $entityManager->persist($game);
    // dd($game);
    $entityManager->persist($currentPlayer);
    $entityManager->persist($card);
    $entityManager->flush();

    return $this->redirectToRoute('app_play');
  }
  
  #[Route('/draw/{playerId}', name:'app_draw_card', requirements: ['playerId'=>'\d+'], methods: ['GET'])]
  public function playerDrawCard(int $playerId, PlayerRepository $playerRepository, GameService $gameService, EntityManagerInterface $entityManager) {
    // 1- find the player
    $currentPlayer = $playerRepository->findOneBy(['id' => $playerId]);
    // if no player or not human  -> error
    if(!isset($currentPlayer) || !($currentPlayer->isHuman())) {
      return $this->redirectToRoute('app_play', ['nextPlayerId' => $currentPlayer]);
    }
    // get the game
    $game = $currentPlayer->getGame();
    $turn = $game->getTurn();
    // metho draw
    $gameService->getRandomCards(1, $game, $entityManager, $currentPlayer);
    $nextTurn = $this->implementTurn($game, $turn);
    $nextPlayerId = $this->getNextPlayerId($currentPlayer, $game, $nextTurn);
    $game->setIdPlayerNextTurn($nextPlayerId);
    $game->setUpdatedAt(new DateTime());
    $entityManager->persist($game);
    $entityManager->persist($currentPlayer);
    $entityManager->flush();
    return $this->redirectToRoute('app_play', ['nextPlayerId' => $nextPlayerId]);
  }

  private function implementTurn(Game $game, int $turn): int
  {
    $nextTurn = ($turn + 1) % 4;
    $game->setTurn($nextTurn);
    return $nextTurn;
  }

  private function canPlayCard(Card $card, Game $game): bool
  {
    return $card->getColor() === $game->getCurrentColor() || $card->getLabel() === $game->getCurrentValue();
  }

  private function specialCardsEffect(Card $card) {
    $currentGame = $card->getGame();
    if($card->getLabel() == "X" || $card->getLabel() == "S" || $card->getLabel() == "+2" ) {
      if($card->getLabel() == "X") {
        return $currentGame->setTurn((($currentGame->getTurn()) + 1) % 4);
      } else if($card->getLabel() == "+2") {

        return;
      } /* else if ($card->getLabel() == "S") {
        return $currentGame->setDirection(!$currentGame->isDirection());
      } */
    }
  }

  private function getNextPlayerId(Player $humanPlayer, Game $game, int $turn){
    dump($turn);
    if($humanPlayer->isHuman() && $game->isDirection()){
      return ($humanPlayer->getId() + (($turn+ 1)% 4));
    } else if ($humanPlayer->isHuman() && !$game->isDirection()) {
      return ($humanPlayer->getId() + (($turn + 3) % 4));
    }
  }
}
