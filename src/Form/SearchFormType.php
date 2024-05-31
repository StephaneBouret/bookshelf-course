<?php

namespace App\Form;

use App\Entity\Author;
use App\Data\SearchData;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher un livre'
                ]
            ])
            ->add('categories', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Category::class,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => function (Category $category): string {
                    return $category->getName();
                }
            ])
            ->add('author', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par son nom'
                ]
            ])
            ->add('minPublicationAt', DateTimeType::class, [
                'label' => false,
                'required' => false,
                'format' => 'yyyy',
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('maxPublicationAt', DateTimeType::class, [
                'label' => false,
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy',
                'html5' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
