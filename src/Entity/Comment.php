<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
/*use Symfony\Component\Serializer\Annotation\Groups;*/
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['this_comment','all_comment','this_game','all_games'])]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    #[Groups(['this_comment','all_comment','this_game','all_games'])]
    private ?string $commentText = null;

    #[ORM\Column(length: 100)]
    #[Groups(['this_comment','all_comment','this_game','all_games'])]
    private ?string $commentUser = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['this_comment','all_comment'])]
    private ?Game $f_commentGameId = null;

    #[ORM\Column(length: 10)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentText(): ?string
    {
        return $this->commentText;
    }

    public function setCommentText(string $commentText): self
    {
        $this->commentText = $commentText;

        return $this;
    }

    public function getCommentUser(): ?string
    {
        return $this->commentUser;
    }

    public function setCommentUser(string $commentUser): self
    {
        $this->commentUser = $commentUser;

        return $this;
    }

    public function getFCommentGameId(): ?game
    {
        return $this->f_commentGameId;
    }

    public function setFCommentGameId(?game $f_commentGameId): self
    {
        $this->f_commentGameId = $f_commentGameId;
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
}
