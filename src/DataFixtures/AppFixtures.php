<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Faker\Factory;
use App\Entity\Post;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($j=1; $j<=5 ; $j++) {
            $category = new Category();
            $category->setTitle($faker->word);
            $manager->persist($category);
            //add post
            for ($i=1; $i<=7;$i++) {
            $post = new Post();
            $post->setTitle($faker->sentence(3));
            $post->setContent($faker->text(500));
            $post->setCreatedAt($faker->dateTimeBetween('-3 months'));
            $post->setCategory($category);
            $manager->persist($post);
            }
        }

       
        $manager->flush();
    }
}
