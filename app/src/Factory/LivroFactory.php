<?php

namespace App\Factory;

use App\Entity\Livro;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Livro>
 */
final class LivroFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Livro::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'anoPublicacao' => (string) self::faker()->numberBetween(1980, (int) date('Y')),
            'edicao' => self::faker()->numberBetween(1, 10),
            'editora' => mb_substr(self::faker()->company(), 0, 40),
            'titulo' => rtrim(mb_substr(self::faker()->sentence(4), 0, 40), '. '),
            'valor' => self::faker()->randomFloat(2, 19.9, 349.9),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Livro $livro): void {})
        ;
    }
}
