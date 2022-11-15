<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
/*use Symfony\Component\Serializer\SerializerInterface;*/
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CommentController extends AbstractController
{
    /* #[Route('/comment', name: 'app_comment')]
     public function index(): JsonResponse
     {
         return $this->json([
             'message' => 'Welcome to your new controller!',
             'path' => 'src/Controller/CommentController.php',
         ]);
     }*/

    /**************************/
    /*[GET ALL COMMENT]*/
    #[Route ('/api/comment/all', name: 'comment_all')]
    public function get_all_comment(CommentRepository      $repository,
                                    SerializerInterface    $serializer,
                                    TagAwareCacheInterface $cache,
    ): JsonResponse
    {
        // $idCache = "commentCacheAll";
        // $comment = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
        //     echo 'Hello comment';
        //     $item->tag('commentCache');
        //     $comment = $repository->findAll();
        //     $context = SerializationContext::create()->setGroups(['all_comment']);
        //     return $serializer->serialize($comment, 'json', $context);
        //     /*return $serializer->serialize($comment, 'json', ['groups' => 'all_comment']);*/
        // });

        $idCache = "commentCacheFindBy";
        $comment = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
            echo 'Hello comment';
            $item->tag('commentCache');
            $comment = $repository->findGameByComment(true, 1, 3);
            $context = SerializationContext::create()->setGroups(['all_comment']);
            return $serializer->serialize($comment, 'json', $context);
            /*return $serializer->serialize($comment, 'json', ['groups' => 'all_comment']);*/
        });

        // $comment = $repository->findAll();
        // format to json
        /*$jsonComment = $serializer->serialize($comment, 'json', ['groups' => 'all_comment']);
        return new JsonResponse($jsonComment, Response::HTTP_OK, [], true);*/
        return new JsonResponse($comment, Response::HTTP_OK, [], true);
    }

    /**************************/
    /*[GET THIS COMMENT]*/
    #[Route ("/api/comment/{idComment}", name: "comment.get", methods: ["GET"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function get_comment(Comment $comment, SerializerInterface $serializer,
                                TagAwareCacheInterface $cache): JsonResponse
    {
        // format to json
        /*$jsonComment = $serializer->serialize($comment, 'json', ['groups' => 'this_comment']);*/

        $commentId = $comment->getId();
        $idCache = "commentThisComment$commentId";

        $comment = $cache->get($idCache, function (ItemInterface $item) use ($comment, $serializer) {
            $item->tag('commentCache');
            /*return $serializer->serialize($comment, 'json', ['groups' => 'this_comment']);*/
            $context = SerializationContext::create()->setGroups(['this_comment']);
            return $serializer->serialize($comment, 'json', $context);
        });

        return new JsonResponse($comment, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**************************/
    /*[CREATE COMMENT]*/
    #[Route ("/api/comment/", name: "comment.post", methods: ["POST"])]
    public function set_comment(
        Request                $request,
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer,
        UrlGeneratorInterface  $urlGenerator,
        TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json', [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]);
       /* $updateComment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $comment->setCommentText($updateComment->getCommentText()?$updateComment->getCommentText():$comment->getCom mentText());\*/
        $comment->setStatus('on');
        $entityManager->persist($comment);
        $entityManager->flush();
        $location = $urlGenerator->generate('comment.get', ['idGame' => $comment->getId()]);
        $jsonGame = $serializer->serialize($location, "json");
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }

    /**************************/
    /*[EDITE THIS COMMENT]*/
    #[Route ("/api/comment/{idComment}", name: "comment.put", methods: ["PUT"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function update_comment(Comment                $comment,
                                   Request                $request,
                                   EntityManagerInterface $entityManager,
                                   SerializerInterface    $serializer,
                                   TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $comment->setStatus('on');
        $entityManager->persist($comment);
        $entityManager->flush();
        $jsonGame = $serializer->serialize($comment, "json");

        return new JsonResponse($jsonGame, Response::HTTP_ACCEPTED, [], true);
    }

    /**************************/
    /*[DELETE THIS COMMENT]*/
    #[Route ("/api/comment/{idComment}", name: "comment.del", methods: ["DELETE"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function delete_comment(Comment                $comment,
                                   EntityManagerInterface $entityManager,
                                   TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $entityManager->remove($comment);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

    /**************************/
    /*[REMOVE THIS COMMENT]*/
    #[Route ("/api/comment/{idComment}", name: "comment.remove", methods: ["POST"])]
    #[ParamConverter("comment", class: "App\Entity\Comment", options: ["id" => "idComment"])]
    public function remove_comment(Comment                $comment,
                                   EntityManagerInterface $entityManager,
                                   TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $comment->setStatus('off');
        $entityManager->persist($comment);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

}
