<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Game;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CommentController.php',
        ]);
    }

    /**************************/
    /*[GET ALL COMMENT]*/
    #[Route ('/comment/all', name: 'comment_all')]
    public function get_all_comment(CommentRepository   $repository,
                                    SerializerInterface $serializer): JsonResponse
    {
        $comment = $repository->findAll();
        // format to json
        $jsonComment = $serializer->serialize($comment, 'json', ['group' => 'all_comment']);
        return new JsonResponse($jsonComment, Response::HTTP_OK, [], true);
    }

    /**************************/
    /*[GET THIS COMMENT]*/
    #[Route ("/comment/{idComment}", name: "comment.get", methods: ["GET"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function get_comment(Comment $comment, SerializerInterface $serializer): JsonResponse
    {
        // format to json
        $jsonComment = $serializer->serialize($comment, 'json', ['groups' => 'this_comment']);

        return new JsonResponse($jsonComment, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**************************/
    /*[CREATE COMMENT]*/
    #[Route ("/comment/game/{game_id}", name: "comment.post", methods: ["POST"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "game_id"])]
    public function set_comment(Game                   $game,
                                Request                $request,
                                EntityManagerInterface $entityManager,
                                SerializerInterface    $serializer,
                                UrlGeneratorInterface  $urlGenerator
    ): JsonResponse
    {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json', []);
        $comment->setStatus('on');
        dump($game->getId());
        $comment->setFCommentGameId($game->getId());
        $entityManager->persist($comment);
        $entityManager->flush();
        $location = $urlGenerator->generate('comment.get', ['idGame' => $comment->getId()]);
        $jsonGame = $serializer->serialize($location, "json");
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }

    /**************************/
    /*[EDITE THIS COMMENT]*/
    #[Route ("/comment/{idComment}", name: "comment.put", methods: ["PUT"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]

    public function update_comment(Comment                $comment,
                                 Request                $request,
                                 EntityManagerInterface $entityManager,
                                 SerializerInterface    $serializer
    ): JsonResponse
    {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $comment->setStatus('on');
        $entityManager->persist($comment);
        $entityManager->flush();
        $jsonGame = $serializer->serialize($comment, "json");

        return new JsonResponse($jsonGame, Response::HTTP_ACCEPTED, [], true);
    }

    /**************************/
    /*[DELETE THIS COMMENT]*/
    #[Route ("/comment/{idComment}", name: "comment.del", methods: ["DELETE"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function delete_comment(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($comment);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

    /**************************/
    /*[REMOVE THIS COMMENT]*/
    #[Route ("/comment/{idComment}", name: "comment.remove", methods: ["POST"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function remove_comment(Comment                $comment,
                                 EntityManagerInterface $entityManager): JsonResponse
    {
        $comment->setStatus('off');
        $entityManager->persist($comment);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

}
