<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')

            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'required' => true,
            ])

            ->add('releaseYear', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 1970,
                    'max' => 2100,
                    'placeholder' => 'ex: 2025',
                ],
            ])

            ->add('category', ChoiceType::class, [
                'required' => false, // ✅ IMPORTANT car nullable en BDD
                'choices' => [
                    'Action' => 'Action',
                    'Adventure' => 'Adventure',
                    'RPG' => 'RPG',
                    'Open World' => 'Open World',
                    'Sport' => 'Sport',
                    'Strategy' => 'Strategy',
                    'Simulation' => 'Simulation',
                    'Puzzle' => 'Puzzle',
                    'Horror' => 'Horror',
                    'Multiplayer' => 'Multiplayer',
                ],
                'placeholder' => 'Choose a category',
            ])

            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPG/PNG/WebP)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '4M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Choisis une image valide (JPG/PNG/WebP).',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}