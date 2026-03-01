<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GameFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ====== ADMIN AUTO ======
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_USER']);

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );

        $manager->persist($admin);

        // ====== TES JEUX (TOUS) ======
        $games = [
            [
                'title' => 'Elden Ring',
                'description' => 'Action-RPG en monde ouvert, combats exigeants et exploration.',
                'image' => 'elden-ring.jpg',
                'price' => 49.99,
            ],
            [
                'title' => 'FIFA 24',
                'description' => 'Jeu de football avec modes Ultimate Team, Carrière et plus.',
                'image' => 'fifa-24.jpg',
                'price' => 39.99,
            ],
            [
                'title' => 'GTA V',
                'description' => 'Open-world criminel, histoire + GTA Online.',
                'image' => 'gta-v.jpg',
                'price' => 19.99,
            ],
            [
                'title' => 'Minecraft',
                'description' => 'Création, survie, exploration, et multijoueur.',
                'image' => 'minecraft.jpg',
                'price' => 24.99,
            ],
            [
                'title' => 'Call of Duty',
                'description' => 'FPS nerveux, multi, et campagnes intenses.',
                'image' => 'cod.jpg',
                'price' => 59.99,
            ],
            [
                'title' => 'The Witcher 3',
                'description' => 'RPG narratif, quêtes, combats, monde vivant.',
                'image' => 'witcher-3.jpg',
                'price' => 14.99,
            ],
        ];

        foreach ($games as $data) {
            $game = new Game();
            $game->setTitle($data['title']);
            $game->setDescription($data['description']);
            $game->setImage($data['image']);
            $game->setPrice($data['price']);

            $manager->persist($game);
        }

        $manager->flush();
    }
}