<?php

namespace App\Controller;
use App\Entity\Game;
use App\Repository\GameRepository;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


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

    #[Route ('/game/all',name: 'app_game')]
    public function get_all_games(GameRepository $repository,
    SerializerInterface $serializer): JsonResponse
    {
        $game = $repository->findAll();
        // format to json
        $jsonGmae = $serializer->serialize($game,'json',);
        return new JsonResponse($jsonGmae,Response::HTTP_OK,[],true);
    }

    #[Route ("/game/{idGame}",name: "game.get",methods: ["GET"])]
    #[ParamConverter("game", class: "App\Entity\Game", options: ["id"=>"idGame"])]
    public function get_games(Game $game, SerializerInterface $serializer): JsonResponse
    {
        //dd($idGame);

        // format to json
        $jsonGame = $serializer->serialize($game,'json',['groups'=>'this_game']);

        return  new JsonResponse($jsonGame,Response::HTTP_OK,['accept'=>'json'],true);
    }
}
