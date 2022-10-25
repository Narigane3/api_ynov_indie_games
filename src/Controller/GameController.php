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
    /** Return all games on db
     * @param GameRepository $repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route ('api/game/all', name: 'app_game')]
    #[IsGranted('ROLE_USER', message:'ha ta pas les droits mon potes')]
    #[IsGranted('ROLE_ADMIN', message:'ha ta pas les droits mon potes')]
    public function get_all_games(GameRepository      $repository,
                                  SerializerInterface $serializer,
                                  Request             $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',5);
        $limit = $limit > 50 ? 20: $limit;
        $status = $request->get('status', 'on');
        /*dd([
            "page"=> $page,
            "limit"=> $limit
        ]);*/
        $game = $repository->findAlLGame($page,$limit,$status);
        // format to json
        $jsonGame = $serializer->serialize($game, 'json', ['groups' => 'all_games']);
        return new JsonResponse($jsonGame, Response::HTTP_OK, [], true);
    }
    /**************************/
    /*[GET THIS GAME]*/
    #[Route ("api/game/{idGame}", name: "game.get", methods: ["GET"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    #[IsGranted('ROLE_USER', message:'RHA RHA ta pas les droits mon potes')]
    #[IsGranted('ROLE_ADMIN', message:'ha ta pas les droits mon potes')]
    public function get_games(Game $game, SerializerInterface $serializer): JsonResponse
    {
        //dd($idGame);

        // format to json
        $jsonGame = $serializer->serialize($game, 'json', ['groups' => 'this_game']);

        return new JsonResponse($jsonGame, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**************************/
    /*[CREAT GAME]*/
    #[Route ("api/game/", name: "game.post", methods: ["POST"])]
    #[IsGranted('ROLE_ADMIN', message:'ha ta pas les droits mon potes')]
    #[IsGranted('ROLE_ADMIN', message:'ha ta pas les droits mon potes')]
    public function set_games(Request                $request,
                              EntityManagerInterface $entityManager,
                              SerializerInterface    $serializer,
                              UrlGeneratorInterface  $urlGenerator,
                              ValidatorInterface     $validator
    ): JsonResponse
    {

        $game = $serializer->deserialize($request->getContent(), Game::class, 'json');
        $game->setStatus('on');
        $error = $validator->validate($game);
        if ($error->count() > 0) {
            return new JsonResponse($serializer->serialize($error,'json'), Response::HTTP_BAD_REQUEST);
        }
        $entityManager->persist($game);
        $entityManager->flush();
        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()]);
        $jsonGame = $serializer->serialize($location, "json");
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, [], true);
    }

    /**************************/
    /*[EDITE THIS GAME]*/
    #[Route ("api/game/{idGame}", name: "game.put", methods: ["PUT"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    #[IsGranted('ROLE_ADMIN', message:'ha ta pas les droits mon potes')]
    public function update_games(Game                   $game,
                                 Request                $request,
                                 EntityManagerInterface $entityManager,
                                 SerializerInterface    $serializer
    ): JsonResponse
    {
        $gameEdite = $serializer->deserialize($request->getContent(), Game::class, 'json',[AbstractNormalizer::OBJECT_TO_POPULATE=>$game]);
        $gameEdite->setId($game->getId());
        $gameEdite->setStatus('on');
        $entityManager->persist($gameEdite);
        $entityManager->flush();
        $jsonGame = $serializer->serialize($game, "json");

        return new JsonResponse($jsonGame, Response::HTTP_ACCEPTED, [], true);
    }

    /**************************/
    /*[DELETE THIS GAME]*/
    #[Route ("api/game/{idGame}", name: "game.del", methods: ["DELETE"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    #[IsGranted('ROLE_SUADMIN', message:'ha ta pas les droits mon potes')]
    public function delete_games(Game $game, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($game);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

    /**************************/
    /*[REMOVE THIS GAME]*/
    #[Route ("api/game/{idGame}", name: "game.remove", methods: ["POST"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id" => "idGame"])]
    #[IsGranted('ROLE_ADMIN', message:'ha ta pas les droits mon potes')]
    public function remove_games(Game                   $game,
                                 EntityManagerInterface $entityManager): JsonResponse
    {
        $game->setStatus('off');
        $entityManager->persist($game);
        $entityManager->flush(); // flush call exc
        return new JsonResponse(NULL, Response::HTTP_NO_CONTENT, [], false);
    }

}
