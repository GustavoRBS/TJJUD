<?php

namespace App\DataFixtures;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Factory\AssuntoFactory;
use App\Factory\AutorFactory;
use App\Factory\LivroFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $autores = AutorFactory::createMany(10);
        $assuntos = AssuntoFactory::createMany(10);

        LivroFactory::createMany(20, function () use ($autores, $assuntos) {
            return [
                'autores' => $this->randomSubset($autores, 1, 3),
                'assuntos' => $this->randomSubset($assuntos, 1, 2),
            ];
        });
    }

    /**
     * @param list<Autor>|list<Assunto> $pool
     * @return list<Autor>|list<Assunto>
     */
    private function randomSubset(array $pool, int $min, int $max): array
    {
        $count = random_int($min, min($max, count($pool)));
        $keys = array_rand($pool, $count);
        $keys = is_array($keys) ? $keys : [$keys];

        return array_map(static fn (int|string $key) => $pool[$key], $keys);
    }
}
