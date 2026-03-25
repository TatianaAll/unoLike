<?php
namespace App\Services;

use App\Entity\Card;
use App\Entity\Game;
use App\Entity\Player;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class GameService {
  public function initGame(EntityManagerInterface $entityManager) {
    // initialisation of the game so => create a new Game instance
    $newGame = new Game();
    $newGame->setCreatedAt(new DateTimeImmutable());
    $newGame->setCurrentColor(null);
    $newGame->setCurrentValue(null);
    $newGame->setDirection(true);
    $newGame->setUpdatedAt(null);

    // create 4 players: the first is human, others are botd
    $players = [];
    for ($incrementPlayer = 0; $incrementPlayer < 4; $incrementPlayer++) {
      // create a new player
      $player = new Player();
      $player->setIsHuman($incrementPlayer === 0);
      $player->setCardsInHand(0);
      $player->setGame($newGame);

      // persist player in DB
      $entityManager->persist($player);
      
      // add player to my list of players
      $players[] = $player;
      
      // add the player in the new game created
      $newGame->addPlayer($player);
    }
    // Persist the game
    $entityManager->persist($newGame);

    // Draw 7 random cards for each player
    foreach ($players as $player) {
      $cards = $this->getRandomCards(7, $newGame, $player, $entityManager);
      $player->setCardsInHand(count($cards));
    }

    // Save in DB
    $entityManager->flush();

    return $newGame;
  }

  public function getRandomCards(int $numberOfCards, Game $game, Player $player, EntityManagerInterface $entityManager) {
    // Create random cards for a player
    $cardsLabel = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "X", "S", "+2"];
    $cardColor = ["green", "red", "yellow", "blue"];
    $randomCards = [];
    for ($cardToCreate = 0; $cardToCreate < $numberOfCards; $cardToCreate++) {
      // select a random color and label for a new card
      $labelNewCard = $cardsLabel[array_rand($cardsLabel)];
      $colorNewCard = $cardColor[array_rand($cardColor)];

      // create the new card in db
      $newCard = new Card();
      $newCard->setColor($colorNewCard);
      $newCard->setLabel($labelNewCard);
      $newCard->setGame($game);
      $newCard->setPlayer($player);
      $entityManager->persist($newCard);
      
      $randomCards[] = $newCard;
    }
    // save in DB
    $entityManager->flush();
    // return the randomly generated cards
    return $randomCards;
  }
}