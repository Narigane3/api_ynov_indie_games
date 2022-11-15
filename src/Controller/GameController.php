<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
/*use Symfony\Component\Serializer\SerializerInterface;*/
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GameController extends AbstractController
{
    /**************************/
    #[Route('/api/game/random', name: 'game.random', methods: ['GET'])]
    public function get_random_game_genre(
        GameRepository $gameRepository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse
    {
        $games = $gameRepository->randomGame($request->query->get('genre', 'RPG'));
        $randGameNum = rand(0, count($games) - 1);
        return new JsonResponse(
            $serializer->serialize($games[$randGameNum], 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }


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
    /** Return all games on db
     * @param GameRepository $repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route ('/api/game/all', name: 'app_game')]
    public function get_all_games(
        GameRepository         $repository,
        SerializerInterface    $serializer,
        Request                $request,
        TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 50 ? 20 : $limit;
        $status = $request->get('status', 'on');
        /*dd([
            "page"=> $page,
            "limit"=> $limit
        ]);*/
        /*$game = $repository->findAlLGame($page,$limit,$status);
        // format to json
        $jsonGame = $serializer->serialize($game, 'json', ['groups' => 'all_games']);
        return new JsonResponse($jsonGame, Response::HTTP_OK, [], true);*/

        $idCache = 'getAllGame';
        /**
         * return a cache object
         */
        $game = $cache->get($idCache, function (ItemInterface $item) use ($repository, $page, $limit, $status, $serializer) {
            echo 'hello cache';
            $item->tag("gameCache");
            $game = $repository->findAll($page, $limit, $status);
            $context = SerializationContext::create()->setGroups(['all_games']);
            return $serializer->serialize($game, 'json', $context);
        });

        return new JsonResponse($game, Response::HTTP_OK, [], true);
    }

    /**************************/
    /*[GET THIS GAME]*/
    #[Route ('/api/game/{idGame}', name: 'game.get', methods: ['GET'])]
    #[ParamConverter('game', class: 'App\Entity\Game', options: ['id' => 'idGame'])]
    public function get_games(
        Game                   $game,
        SerializerInterface    $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse
    {
        $gameId = $game->getId();
        $idCache = "getThisGame$gameId";
        $game = $cache->get($idCache, function (ItemInterface $item) use ($serializer,$game,$gameId) {
            echo "hello cache";
            $item->tag("gameCache");
            $context = SerializationContext::create()->setGroups('this_game');
            return $serializer->serialize($game, 'json', $context);
        });

        // format to json
        /*$jsonGame = $serializer->serialize($game, 'json', ['groups' => 'this_game']);
        return new JsonResponse($jsonGame, Response::HTTP_OK, ['accept' => 'json'], true);*/
        return new JsonResponse($game, Response::HTTP_OK, ['accept' => 'json'], true);
    }


    /**************************/
    /*[CREAT GAME]*/
    #[Route ('/game', name: 'game.post', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    public function set_games(
        Request                $request,
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer,
        UrlGeneratorInterface  $urlGenerator,
        ValidatorInterface     $validator,
        TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['gameCache']);
        $game = $serializer->deserialize($request->getContent(), Game::class, 'json');
        $game->setStatus('on');
        $error = $validator->validate($game);
        if ($error->count() > 0) {
            return new JsonResponse($serializer->serialize($error, 'json'), Response::HTTP_BAD_REQUEST);
        }
        $entityManager->persist($game);
        $entityManager->flush();
        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()]);
        $jsonGame = $serializer->serialize($location, "json");
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }


    /**************************/
    /*[EDITE THIS GAME]*/
    #[Route ('/api/game/{idGame}', name: 'game.put', methods: ['PUT'])]
    #[ParamConverter('game', options: ['id' => 'idGame'])]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    public function update_games(
        Game                   $game,
        Request                $request,
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer,
        UrlGeneratorInterface  $urlGenerator,
        TagAwareCacheInterface $cache,
    ): JsonResponse
    {
        $cache->invalidateTags(['gameCache']);
        /*$game = $serializer->deserialize(
            $request->getContent(),
            Game::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $game]
        );*/

        $updateGame = $serializer->deserialize(
            $request->getContent(),
            Game::class,
            'json'
        );

        $game->setGameName($updateGame->getGameName()?$updateGame->getGameName():$game->getGameName());
        $game->setGameCompany($updateGame->getGameCompany()?$updateGame->getGameCompany():$game->getGameCompany());
        $game->setGamePlatform($updateGame->getGamePlatform()?$updateGame->getGamePlatform():$game->getGamePlatform());
        $game->setGameDescription($updateGame->getGameDescription()?$updateGame->getGameDescription():$game->getGameDescription());
        $game->setGenre($updateGame->getGenre()?$updateGame->getGenre():$game->getGenre());
        $game->setGameLaunchDate($updateGame->getGameLaunchDate()?$updateGame->getGameLaunchDate():$game->getGameLaunchDate());

        $game->setStatus('on');

        $entityManager->persist($game);
        $entityManager->flush();

        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonGame = $serializer->serialize($game, 'json');
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, ['Location' => $location], true);
    }


    /**************************/
    /*[DELETE THIS GAME]*/
    #[Route ('api/game/{idGame}', name: 'game.del', methods: ['DELETE'])]
    #[ParamConverter('game', class: 'App\Entity\Game', options: ['id' => 'idGame'])]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    public function delete_games(
        Game                   $game,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse
    {
        // remove the cache
        $cache->invalidateTags(['gameCache']);
        $entityManager->remove($game);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }


    /*[REMOVE THIS GAME]*/
    #[Route ('api/game/remove/{idGame}', name: 'game.remove', methods: ['POST'])]
    #[ParamConverter('game', class: 'App\Entity\Game', options: ['id' => 'idGame'])]
    #[IsGranted('ROLE_ADMIN', message: 'Oups : Tu peux pas faire ça !')]
    public function remove_games(
        Game                   $game,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $cache->invalidateTags(['gameCache']);
        $game->setStatus('off');
        $entityManager->persist($game);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }
}
