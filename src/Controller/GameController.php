<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GameController.php',
        ]);
    }
    /**************************/
    /*[GET ALL GAME]*/
    #[Route ('/game/all', name: 'app_game')]
    public function get_all_games(GameRepository      $repository,
                                  SerializerInterface $serializer,
                                  Request             $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',5);
        $limit = $limit > 50 ? 20: $limit;
        /*dd([
            "page"=> $page,
            "limit"=> $limit
        ]);*/
        $game = $repository->findAll();
        $game = $repository->findWithPagination($page,$limit);
        // format to json
        $jsonGmae = $serializer->serialize($game, 'json', ['groups' => 'all_games']);
        return new JsonResponse($jsonGmae, Response::HTTP_OK, [], true);
    }
    /**************************/
    /*[GET THIS GAME]*/
    #[Route ("/game/{idGame}", name: "game.get", methods: ["GET"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    public function get_games(Game $game, SerializerInterface $serializer): JsonResponse
    {
        //dd($idGame);

        // format to json
        $jsonGame = $serializer->serialize($game, 'json', ['groups' => 'this_game']);

        return new JsonResponse($jsonGame, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**************************/
    /*[CREAT GAME]*/
    #[Route ("/game/", name: "game.post", methods: ["POST"])]
    public function set_games(Request                $request,
                              EntityManagerInterface $entityManager,
                              SerializerInterface    $serializer,
                              UrlGeneratorInterface  $urlGenerator,
                              ValidatorInterface     $validator
    ): JsonResponse
    {

        $game = $serializer->deserialize($request->getContent(), Game::class, 'json');
        $game->setStatus('on');

        $errors = $validator->validate($game);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($game);
        $entityManager->flush();
        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()]);
        $jsonGame = $serializer->serialize($location, "json");
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }

    /**************************/
    /*[EDITE THIS GAME]*/
    #[Route ("/game/{id}", name: "game.put", methods: ["PUT"])]
    public function update_games(Game                   $game,
                                 Request                $request,
                                 EntityManagerInterface $entityManager,
                                 SerializerInterface    $serializer
    ): JsonResponse
    {
        $game = $serializer->deserialize($request->getContent(), Game::class, 'json');
        $game->setStatus('on');

        $entityManager->persist($game);

        $entityManager->flush();
        $jsonGame = $serializer->serialize($game, "json");

        return new JsonResponse($jsonGame, Response::HTTP_ACCEPTED, [], true);
    }

    /**************************/
    /*[DELETE THIS GAME]*/
    #[Route ("/game/{idGame}", name: "game.del", methods: ["DELETE"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    public function delete_games(Game $game, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($game);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

    /**************************/
    /*[REMOVE THIS GAME]*/
    #[Route ("/game/{idGame}", name: "game.remove", methods: ["POST"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    public function remove_games(Game                   $game,
                                 EntityManagerInterface $entityManager): JsonResponse
    {
        $game->setStatus('off');
        $entityManager->persist($game);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

}
