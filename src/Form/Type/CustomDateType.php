<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CustomDateType extends AbstractType
{
    public function getParent()
    {
        return DateType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'years' => range(date('Y') - 30, date('Y') + 20), // Adjust the range as needed : -+ 20 years from now
            'widget' => 'single_text', // Prevents rendering it as a set of 3 select inputs
            'html5' => false,
            'format' => 'ddMMMMyyyy', // Adjust the format as needed - https://symfony.com/doc/current/reference/forms/types/date.html#rendering-a-single-html5-text-box
        ]);
    }
}
