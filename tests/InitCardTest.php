<?php

namespace App\Tests;

use App\Entity\Game;
use App\Enum\Status;
use App\Repository\GameRepository;
use App\Services\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class InitCardTest extends TestCase
{
  // launch the tests : php bin/phpunit tests/InitCardTest.php
  public function testInitService(): void
  {
      $this->assertTrue(true);
  }

  public function testCreateNewGameWithExistingGame(): void
  {
    // create a game
    $existingGame = new Game();
    $existingGame->setStatus(Status::IN_PROGRESS);
    // mock repo
    $mockRepo = $this->createMock(EntityRepository::class);
    $mockRepo->expects($this->once())
              ->method('findOneBy')
              ->willReturn($existingGame);

    // Mock entityManager
    $entityManager = $this->createMock(EntityManagerInterface::class);
    $entityManager->method('getRepository')->willReturn($mockRepo);

    // Try if persist call correctly
    $entityManager->expects($this->once())->method('persist')->with($existingGame);

    // Test initiation to set ancien game to finish and the new to in progress
    $game = $entityManager->getRepository(Game::class)->findOneBy(['status' => Status::IN_PROGRESS]);
    if ($game) {
        $game->setStatus(Status::FINISHED);
        $entityManager->persist($game);
    }
    // Asserting
    $this->assertEquals(Status::FINISHED, $existingGame->getStatus());
    }
}

