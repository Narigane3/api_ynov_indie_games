<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /****
     * @var Generator
     */
    private Generator $faker;

    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $userNumber = 10;

        // Authentication Admin
        $adminUser = new User();
        $password = 'password';
        $adminUser->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->userPasswordHasher->hashPassword($adminUser, $password));
        $manager->persist($adminUser);


        // Authentication Users
        for ($i=0; $i < $userNumber; $i++) {
            $userUser = new User();
            $password = $this->faker->password(2, 6);
            $userUser->setUsername($this->faker->userName() . '@' . $password)
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->userPasswordHasher->hashPassword($userUser, $password));
            $manager->persist($userUser);
        }

        // $product = new Product();
        // $manager->persist($product);
        $gameGenres = ['RPG', 'MMO', 'FPS', 'ACTION', 'ADVENTURE', 'SPORT', 'RACE'];
        $gameList=[];
        for ($i=0; $i < 50;$i++){
            $game = new Game();
            $game
                ->setGameName($this->faker->name())
                ->setGameCompany($this->faker->company())
                ->setGameLaunchDate($this->faker->optional($weight = .25)->dateTime($max = '01/01/2003'))
                ->setGamePlatform('PC')
                ->setGameDescription($this->faker->realText(200))
                ->setGenre($gameGenres[array_rand($gameGenres)])
                ->setStatus('on');
            $gameList[] = $game;
            $manager->persist($game); // stock in php cache
        }


        for ($i=0; $i < 50;$i++){
            $comment = new Comment();
            $comment
                ->setCommentText($this->faker->realText(500))
                ->setCommentUser($this->faker->userName())
                ->setStatus('on')
                ->setFCommentGameId($gameList[array_rand($gameList)]);
            $manager->persist($comment);
        }

        $manager->flush();
    }
}
