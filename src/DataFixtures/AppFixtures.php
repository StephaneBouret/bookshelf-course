<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Book;
use App\Entity\Author;
use DateTimeImmutable;
use App\Entity\Category;
use App\Entity\State;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bezhanov\Faker\Provider\Educator($faker));
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));

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
