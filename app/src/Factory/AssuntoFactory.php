<?php

namespace App\Factory;

use App\Entity\Assunto;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Assunto>
 */
final class AssuntoFactory extends PersistentObjectFactory
{
    private const ASSUNTOS = [
        'Romance', 'Ficção Científica', 'Tecnologia', 'História', 'Biografia',
        'Infantil', 'Poesia', 'Terror', 'Suspense', 'Programação',
        'Filosofia', 'Educação', 'Culinária', 'Autoajuda', 'Fantasia',
        'Aventura', 'Economia', 'Direito', 'Medicina', 'Matemática',
    ];

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
        return Assunto::class;
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
            'descricao' => self::faker()->unique()->randomElement(self::ASSUNTOS),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Assunto $assunto): void {})
        ;
    }
}
