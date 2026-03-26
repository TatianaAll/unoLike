<?php

namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\GameRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClickController extends AbstractController
{
  #[Route('/click/{cardId}', name: 'app_click', requirements: ['cardId'=>'\d+'], methods: ['GET'])]
  public function playCard(int $cardId, CardRepository $cardRepository, EntityManagerInterface $entityManager): Response
  {
    // if a card is played i wanna to change the player_id to null and update the updated at in Game
    $cardPlayed = $cardRepository->findOneBy(['id' => $cardId]);
    $gameInProgress = $cardPlayed->getGame();

    // the current card is discard so the player = null;
    $cardPlayed->setPlayer(null);

    // Setting the new color and value played
    $gameInProgress->setCurrentColor($cardPlayed->getColor());
    $gameInProgress->setCurrentValue($cardPlayed->getLabel());
    // Setting the updated at at the current time
    $gameInProgress->setUpdatedAt(new DateTime());

    $entityManager->persist($gameInProgress);
    $entityManager->persist($cardPlayed);
    $entityManager->flush();
    
    return $this->redirectToRoute('app_play');
  }
}
