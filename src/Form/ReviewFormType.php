<?php

namespace App\Form;

use App\Enum\ReviewDecision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach (ReviewDecision::cases() as $case) {
            if (ReviewDecision::PENDING === $case) {
                continue;
            }
            $choices[$case->label()] = $case->value;
        }

        $builder
            ->add('decision', ChoiceType::class, [
                'label' => 'Decyzja',
                'choices' => $choices,
                'constraints' => [new NotBlank()],
            ])
            ->add('score', IntegerType::class, [
                'label' => 'Ocena (1-10)',
                'constraints' => [new NotBlank(), new Range(min: 1, max: 10)],
            ])
            ->add('comments', TextareaType::class, [
                'label' => 'Komentarz dla autora / redakcji',
                'constraints' => [new NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
