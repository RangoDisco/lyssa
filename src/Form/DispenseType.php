<?php

namespace App\Form;

use App\Entity\Dispense;
use App\Entity\Medicine;
use App\Entity\Prescription;
use App\Entity\Variant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DispenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scheduledAt', null, [
                'widget' => 'single_text',
            ])
            ->add('medicine', EntityType::class, [
                'class' => Medicine::class,
                'choice_label' => function (Medicine $medicine) {
                    return sprintf("%s - %s", $medicine->getName(), $medicine->getLab()?->getName());
                },
            ])
            ->add('prescription', EntityType::class, [
                'class' => Prescription::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dispense::class,
        ]);
    }
}
