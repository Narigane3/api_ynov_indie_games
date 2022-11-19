<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
/*use Symfony\Component\Serializer\SerializerInterface;*/

class CommentController extends AbstractController
{
    /**
     * Returns all comments tied with their own game.
     *
     * This call will return all comments with their attatched games
     */
    #[Route('/api/comments', name: 'comment_all', methods: ['GET'])]
    #[OA\Parameter(
        name: 'page',
        description: 'The field is offset page of content',
        in: 'query',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'The limit of comments by page',
        in: 'query',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns all comments tied their own game',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Comment::class))
        )
    )]
    #[Security(name: 'Bearer')]
    public function get_all_comment(
        Request                 $request,
        CommentRepository       $repository,
        SerializerInterface     $serializer,
        TagAwareCacheInterface  $cache
    ): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 3);

        $idCache = 'commentCacheFindBy';
        $comment = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $page, $limit) {
            $item->tag('commentCache');
            $comment = $repository->findAllByPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(['all_comment']);
            return $serializer->serialize($comment, 'json', $context);
        });

        return new JsonResponse($comment, Response::HTTP_OK, [], true);
    }


    /**
     * Returns a comment that corresponds to the given ID
     *
     * This call will return a comment that matches the ID found in the route's URL
     */
    #[Route ("/api/comment/{idComment}", name: "comment.get", methods: ["GET"])]
    #[OA\Parameter(
        name: 'idComment',
        description: 'The comment id',
        in: 'path',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Response(
        response: 200,
        description: 'Success to return the specified comment content',
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[Security(name: 'Bearer')]
    #[ParamConverter('comment', class: 'App\Entity\Comment', options: ['id' => 'idComment'])]
    public function get_comment(
        Comment                 $comment,
        SerializerInterface     $serializer,
        TagAwareCacheInterface  $cache
    ): JsonResponse
    {
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


    /**
     * Creates (or update) a comment based on given fields and returns it
     *
     * This call will create or update a comment based on the request fields and returns it
     */
    #[Route ("/api/comment", name: "comment.post", methods: ["POST"])]
    #[OA\RequestBody(
        description: 'Content to create comment',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Success to create an comment',
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[Security(name: 'Bearer')]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    public function set_comment(
        Request                 $request,
        EntityManagerInterface  $entityManager,
        SerializerInterface     $serializer,
        UrlGeneratorInterface   $urlGenerator,
        ValidatorInterface      $validator,
        TagAwareCacheInterface  $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
       /* $updateComment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $comment->setCommentText($updateComment->getCommentText()?$updateComment->getCommentText():$comment->getCom mentText());\*/
        $comment->setStatus('on');
        $error = $validator->validate($comment);
        if ($error->count() > 0) {
            return new JsonResponse($serializer->serialize($error, 'json'), Response::HTTP_BAD_REQUEST);
        }
        $entityManager->persist($comment);
        $entityManager->flush();
        $location = $urlGenerator->generate('comment.get', ['idGame' => $comment->getId()]);
        $jsonGame = $serializer->serialize($location, 'json');
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }


    /**
     * Update a comment based on given ID and returns it
     *
     * This call will create or update a comment based on the given ID and returns it
     */
    #[Route('/api/comment/{idComment}', name: 'comment.put', methods: ['PUT'])]
    #[OA\Parameter(
        name: 'idComment',
        description: 'The comment id',
        in: 'path',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\RequestBody(
        description: 'Content to update a comment',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Success to update specified comment',
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[Security(name: 'Bearer')]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    #[ParamConverter('comment', class: 'App\Entity\Comment', options: ['id' => 'idComment'])]
    public function update_comment(
        Comment                 $comment,
        Request                 $request,
        EntityManagerInterface  $entityManager,
        SerializerInterface     $serializer,
        TagAwareCacheInterface  $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $comment->setStatus('on');
        $entityManager->persist($comment);
        $entityManager->flush();
        $jsonGame = $serializer->serialize($comment, 'json');

        return new JsonResponse($jsonGame, Response::HTTP_ACCEPTED, [], true);
    }


    /**
     * Deletes a comment that corresponds to the given ID and returns it
     *
     * This call will delete a comment that matches the given ID and then return it
     */
    #[Route('/api/comment/{idComment}', name: 'comment.del', methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'idComment',
        description: 'The comment id',
        in: 'path',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Response(
        response: 204,
        description: 'Success to remove a specified comment',
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[Security(name: 'Bearer')]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    #[ParamConverter('comment', class: 'App\Entity\Comment', options: ['id' => 'idComment'])]
    public function delete_comment(
        Comment                 $comment,
        EntityManagerInterface  $entityManager,
        TagAwareCacheInterface  $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $entityManager->remove($comment);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }


    /**
     * Sets a comment that corresponds to the given ID as removed in DB and returns it
     *
     * This call will set a comment corresponding to the given ID as removed in the Database return it
     */
    #[Route('/api/comment/{idComment}', name: 'comment.remove', methods: ['POST'])]
    #[OA\Parameter(
        name: 'idComment',
        description: 'The comment id',
        in: 'path',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Response(
        response: 201,
        description: 'Success to deactivate the specified comment',
        content: new OA\JsonContent(
            ref: new Model(type: Comment::class)
        )
    )]
    #[Security(name: 'Bearer')]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    #[ParamConverter('comment', class: 'App\Entity\Comment', options: ['id' => 'idComment'])]
    public function remove_comment(
        Comment                 $comment,
        EntityManagerInterface  $entityManager,
        TagAwareCacheInterface  $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['commentCache']);
        $comment->setStatus('off');
        $entityManager->persist($comment);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }
}
