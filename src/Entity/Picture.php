<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;

#[ORM\Entity(repositoryClass: PictureRepository::class)]

/**
 * @Uploadable()
 */
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getPicture','getAllPicture'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPicture','getAllPicture'])]
    private ?string $realName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPicture','getAllPicture'])]
    private ?string $realPath = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPicture','getAllPicture'])]
    private ?string $publicPath = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getPicture','getAllPicture'])]
    private ?string $mineType = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    /**
     * @UploadableField(mapping="picture", fileNameProperty="realPath")
     */
    private  $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): self
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function getMineType(): ?string
    {
        return $this->mineType;
    }

    public function setMineType(string $mineType): self
    {
        $this->mineType = $mineType;

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

    public function getFile():?File
    {
        return $this->file;
    }
    public function setFile(?File $file): ?Picture
    {
        $this->file= $file;
        return $this;
    }



}
