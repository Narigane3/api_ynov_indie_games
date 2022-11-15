<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

//use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *   "self",
 *    href=@Hateoas\Route(
 *    "game.get",
 *     parameters= {
 *      "idGame" = "expr(object.getId())"
 *     }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="all_games")
 * )
 */
#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    #[Assert\NotBlank(message: "le jeux doit avoir un nom")]
    #[Assert\NotNull]
    private ?string $gameName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    private ?string $gameCompany = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    private ?\DateTimeInterface $gameLaunchDate = null;

    #[ORM\Column(length: 512, nullable: true)]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    private ?string $gameDescription = null;

    #[ORM\Column(length: 100)]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    private ?string $gamePlatform = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "le status doit être déclaré")]
    #[Assert\NotNull]
    #[Assert\Choice(
        choices: ['on', 'off'], message: "Active ou désactive le msg"
    )]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'f_commentGameId', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(['all_games'])]
    private Collection $comments;

    #[ORM\Column(length: 100, nullable: true, options: ['default' => 'RPG'])]
    #[Groups(['this_game', 'all_games', 'this_comment', 'all_comment'])]
    #[Assert\NotBlank(message: "le jeux doit avoir un genre non vide")]
    #[Assert\Choice(
        choices: ['RPG', 'MMO', 'HACK-AND-SLASH', 'FPS', 'BATTLE-ROYAL', 'ADVENTURE', 'RACE', 'MUSIC', 'SIMULATION', 'SPORT'], message: 'Prend un genre dans une list défini'
    )]
    private ?string $genre = 'RPG';

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameName(): ?string
    {
        return $this->gameName;
    }

    public function setGameName(string $gameName): self
    {
        $this->gameName = $gameName;

        return $this;
    }

    public function getGameCompany(): ?string
    {
        return $this->gameCompany;
    }

    public function setGameCompany(string $gameCompany): self
    {
        $this->gameCompany = $gameCompany;

        return $this;
    }

    public function getGameLaunchDate(): ?\DateTimeInterface
    {
        return $this->gameLaunchDate;
    }

    public function setGameLaunchDate(?\DateTimeInterface $gameLaunchDate): self
    {
        $this->gameLaunchDate = $gameLaunchDate;

        return $this;
    }

    public function getGameDescription(): ?string
    {
        return $this->gameDescription;
    }

    public function setGameDescription(?string $gameDescription): self
    {
        $this->gameDescription = $gameDescription;

        return $this;
    }

    public function getGamePlatform(): ?string
    {
        return $this->gamePlatform;
    }

    public function setGamePlatform(string $gamePlatform): self
    {
        $this->gamePlatform = $gamePlatform;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setFCommentGameId($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getFCommentGameId() === $this) {
                $comment->setFCommentGameId(null);
            }
        }

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }
}
