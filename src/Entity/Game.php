<?php

namespace App\Entity;

use App\Repository\GameRepository;
use App\Enum\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $direction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currentColor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currentValue = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\Column]
    private ?int $turn = null;

    #[ORM\Column]
    private ?int $idPlayerNextTurn = null;

    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDirection(): ?bool
    {
        return $this->direction;
    }

    public function setDirection(bool $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function getCurrentColor(): ?string
    {
        return $this->currentColor;
    }

    public function setCurrentColor(?string $currentColor): static
    {
        $this->currentColor = $currentColor;

        return $this;
    }

    public function getCurrentValue(): ?string
    {
        return $this->currentValue;
    }

    public function setCurrentValue(?string $currentValue): static
    {
        $this->currentValue = $currentValue;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setGame($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getGame() === $this) {
                $player->setGame(null);
            }
        }

        return $this;
    }

    public function getTurn(): ?int
    {
        return $this->turn;
    }

    public function setTurn(int $turn): static
    {
        $this->turn = $turn;

        return $this;
    }

    public function getIdPlayerNextTurn(): ?int
    {
        return $this->idPlayerNextTurn;
    }

    public function setIdPlayerNextTurn(int $idPlayerNextTurn): static
    {
        $this->idPlayerNextTurn = $idPlayerNextTurn;

        return $this;
    }
}
