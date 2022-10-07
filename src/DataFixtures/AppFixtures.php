<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    /****
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $gameListe=[];
        for ($i=0; $i < 50;$i++){
            $game = new Game();
            $game
                ->setGameName($this->faker->name())
                ->setGameCompany($this->faker->company())
                ->setGameLaunchDate($this->faker->optional($weight = .25)->dateTime($max = '01/01/2003'))
                ->setGamePlatform('PC')
                ->setGameDescription($this->faker->realText(200))
                ->setStatus('on');
            $gameListe[] = $game;
            $manager->persist($game); // stock in php cache
        }


        for ($i=0; $i < 50;$i++){
            $comment = new Comment();
            $comment
                ->setCommentText($this->faker->realText(500))
                ->setCommentUser($this->faker->userName())
                ->setStatus('on')->setFCommentGameId($gameListe[array_rand($gameListe)]);
            $manager->persist($comment);
        }




        $manager->flush();
    }
}
