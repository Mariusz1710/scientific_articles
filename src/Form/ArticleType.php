<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Tytuł artykułu',
                'constraints' => [new NotBlank()],
            ])
            ->add('authors', TextType::class, [
                'label' => 'Autorzy (oddzieleni przecinkami)',
                'constraints' => [new NotBlank()],
            ])
            ->add('abstract', TextareaType::class, [
                'label' => 'Streszczenie',
                'constraints' => [new NotBlank()],
            ])
            ->add('keywords', TextType::class, [
                'label' => 'Słowa kluczowe (oddzielone przecinkami)',
                'constraints' => [new NotBlank()],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Dział naukowy',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Wybierz dział',
            ])
            ->add('file', FileType::class, [
                'label' => 'Plik artykułu (LaTeX .tex / PDF / DOCX)',
                'mapped' => false,
                'required' => $options['file_required'],
                'constraints' => $options['file_required'] ? [new NotBlank(message: 'Dołącz plik artykułu.')] : [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'file_required' => true,
        ]);
    }
}
