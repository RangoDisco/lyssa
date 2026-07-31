<?php

namespace App\Form;

use App\Entity\Medicine;
use App\Entity\Substance;
use App\Entity\SubstanceCategory;
use App\Entity\User;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('categories', EntityType::class, [
                'class' => SubstanceCategory::class,
                'choice_label' => 'name',
                'multiple' => true
            ])
            ->add('code')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Substance::class,
        ]);
    }
}
