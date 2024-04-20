<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Book;
use App\Entity\Author;
use DateTimeImmutable;
use App\Entity\Category;
use App\Entity\State;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;
    protected $passwordHasher;

    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->slugger = $slugger;
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bezhanov\Faker\Provider\Educator($faker));
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));

        $admin = new User;

        $hash = $this->passwordHasher->hashPassword($admin, "password");
        $admin->setEmail("admin@gmail.com")
            ->setFirstname("admin")
            ->setLastname("admin")
            ->setPassword($hash)
            ->setRoles(['ROLE_ADMIN'])
            ->setDateofbirth(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-10 years')))
            ->setAdress($faker->streetAddress)
            ->setPostalCode($faker->postcode)
            ->setCity($faker->city)
            ->setPhone($faker->mobileNumber());

        $manager->persist($admin);

        for ($u = 0; $u < 5; $u++) {
            $user = new User;
            $hash = $this->passwordHasher->hashPassword($user, "password");
            $user->setEmail("user$u@gmail.com")
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPassword($hash)
                ->setDateofbirth(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-10 years')))
                ->setAdress($faker->streetAddress)
                ->setPostalCode($faker->postcode)
                ->setCity($faker->city)
                ->setPhone($faker->mobileNumber());

            $manager->persist($user);
        }

        $authors = [];
        for ($a = 0; $a < 10; $a++) {
            $author = new Author;
            $author->setFirstname($faker->firstName())
                ->setLastname($faker->lastName());
            $authors[] = $author;

            $manager->persist($author);
        }

        $states = [];
        for ($s = 0; $s < 4; $s++) {
            $state = new State;
            $state->setName($faker->category)
                ->setSlug(strtolower($this->slugger->slug($state->getName())));
            $states[] = $state;

            $manager->persist($state);
        }

        for ($c = 0; $c < 5; $c++) {
            $category = new Category;
            $category->setName($faker->department)
                ->setSlug(strtolower($this->slugger->slug($category->getName())));

            $manager->persist($category);

            for ($b = 0; $b < mt_rand(5, 10); $b++) {
                $book = new Book;
                $book->setName($faker->university)
                    ->setSlug(strtolower($this->slugger->slug($book->getName())))
                    ->setDescription($faker->paragraph())
                    // ->setPicture($faker->imageUrl(200, 200, true))
                    ->setCategory($category)
                    ->setPublicationAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));

                $selectedAuthors = $faker->randomElements($authors, mt_rand(3, 5));
                foreach ($selectedAuthors as $author) {
                    $book->setAuthor($author);
                }

                $selectedStates = $faker->randomElements($states);
                foreach ($selectedStates as $state) {
                    $book->setState($state);
                }

                $manager->persist($book);
            }
        }

        $manager->flush();
    }
}
