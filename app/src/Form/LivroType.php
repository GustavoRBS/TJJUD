<?php

namespace App\Form;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título',
                'attr' => ['maxlength' => 40],
            ])
            ->add('editora', TextType::class, [
                'label' => 'Editora',
                'attr' => ['maxlength' => 40],
            ])
            ->add('edicao', IntegerType::class, [
                'label' => 'Edição',
                'attr' => ['min' => 1],
            ])
            ->add('anoPublicacao', TextType::class, [
                'label' => 'Ano de publicação',
                'attr' => [
                    'maxlength' => 4,
                    'inputmode' => 'numeric',
                    'pattern' => '[0-9]{4}',
                    'placeholder' => 'AAAA',
                ],
            ])
            ->add('valor', MoneyType::class, [
                'label' => 'Valor (R$)',
                'currency' => 'BRL',
            ])
            ->add('autores', EntityType::class, [
                'class' => Autor::class,
                'choice_label' => 'nome',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Autores',
                'attr' => ['size' => 6],
            ])
            ->add('assuntos', EntityType::class, [
                'class' => Assunto::class,
                'choice_label' => 'descricao',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'label' => 'Assuntos',
                'attr' => ['size' => 6],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livro::class,
            'csrf_token_id' => 'livro',
        ]);
    }
}
