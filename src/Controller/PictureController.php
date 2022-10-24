<?php

namespace App\Controller;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PictureController extends AbstractController
{
    #[Route('/picture', name: 'app_picture')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PictureController.php',
        ]);
    }
    #[Route('api/picture', name: 'pictures.creat', methods: ['POST'])]
    public function createPicture(
        Request $request,
        EntityManagerInterface $entityManager,

    ): JsonResponse
    {
        $file = $request->files->get('file');
        $picture = new Picture($file);
        $picture->setFile($file);
        $picture->setMineType($file->getClientMimeType());
        $picture->setRealName($file->getClientOriginalName());
        $picture->setPublicPath(" ");
        $picture->setStatus('on');
        $picture->setRealPath("/");
        $entityManager->persist($picture);
        $entityManager->flush();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PictureController.php',
        ]);
    }
}
