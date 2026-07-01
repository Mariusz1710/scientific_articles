<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Enum\ArticleStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Demo data: run with `bin/console doctrine:fixtures:load` in a dev environment.
 * Default password for every demo account: "password123".
 */
class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [];
        foreach (['Informatyka', 'Matematyka stosowana', 'Elektronika i telekomunikacja', 'Zarządzanie'] as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[$name] = $category;
        }

        $admin = $this->createUser($manager, 'admin@uczelnia.pl', 'Administrator Systemu', ['ROLE_ADMIN']);
        $editor = $this->createUser($manager, 'opiekun@uczelnia.pl', 'Dr Anna Redaktor', ['ROLE_EDITOR']);
        $reviewer1 = $this->createUser($manager, 'recenzent1@uczelnia.pl', 'Prof. Jan Recenzent', ['ROLE_REVIEWER']);
        $reviewer2 = $this->createUser($manager, 'recenzent2@uczelnia.pl', 'Dr Maria Recenzentka', ['ROLE_REVIEWER']);
        $author = $this->createUser($manager, 'autor@uczelnia.pl', 'Mgr Piotr Autor', ['ROLE_AUTHOR']);

        $article = new Article();
        $article->setTitle('Zastosowanie uczenia maszynowego w systemach rekomendacyjnych');
        $article->setAuthors('Piotr Autor, Anna Współautor');
        $article->setAbstract('Artykuł prezentuje przegląd metod uczenia maszynowego stosowanych we współczesnych systemach rekomendacyjnych oraz proponuje własne podejście hybrydowe.');
        $article->setKeywords('uczenie maszynowe, systemy rekomendacyjne, sztuczna inteligencja');
        $article->setCategory($categories['Informatyka']);
        $article->setSubmitter($author);
        $article->setStatus(ArticleStatus::SUBMITTED);
        $manager->persist($article);

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $fullName, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $manager->persist($user);

        return $user;
    }
}
