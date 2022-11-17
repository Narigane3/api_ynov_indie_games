<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

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

    /**
     * Return the picture find by id
     */
    #[Route('api/picture/{idPicture}', name: 'picture.get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Return the picture find',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Picture::class))
        )
    )]
    #[Security(name: 'Bearer')]
    public function getPicture(int                 $idPicture,
                               SerializerInterface $serializer,
                               PictureRepository   $repositor,
                               Request             $request
    ): JsonResponse
    {
        $picture = $repositor->find($idPicture);
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        //$location = $location . str_replace("/assets", "assets", $relativePath);
        if ($picture) {
            return new JsonResponse($serializer->serialize($picture,
                'json',
                ["groups" => 'getPicture']),
                JsonResponse::HTTP_OK,
                ["Location" => $location],
                true
            );
        }
        return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Add new picture
     */
    #[Route('api/picture', name: 'pictures.create', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Picture is add',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Picture::class))
        )
    )]
    #[OA\RequestBody(
        description: 'Content body for add new picture',
        required: true,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Picture::class))
        )
    )]
    #[Security(name: 'Bearer')]
    public function createPicture(
        Request                $request,
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer,
        UrlGeneratorInterface  $urlGenerator

    ): JsonResponse
    {
        $picture = new Picture();

        $file = $request->files->get('file');
        $picture->setFile($file);
        $picture->setMineType($file->getClientMimeType());
        $picture->setRealName($file->getClientOriginalName());
        $picture->setPublicPath("/images/picture");
        $picture->setStatus('on');

        $entityManager->persist($picture);
        $entityManager->flush();
        /* return $this->json([
             'message' => 'Welcome to your new controller!',
             'path' => 'src/Controller/PictureController.php',
         ]);*/
        $location = $urlGenerator->generate('picture.get', ['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonPicture = $serializer->serialize($location, "json", ['getPicture']);
        return new JsonResponse($jsonPicture, Response::HTTP_OK, [], true);
    }
}
