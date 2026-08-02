<?php

namespace App\Form;

use App\Entity\Lab;
use App\Entity\Medicine;
use App\Entity\Substance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedicineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('media', UploadableMedia::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('name')
            ->add('cis')
            ->add('dosage')
            ->add('format')
            ->add('labs', EntityType::class, [
                'class' => Lab::class,
                'choice_label' => 'name',
                'multiple' => true
            ])
            // TODO: handle with join
            ->add('substance', EntityType::class, [
                'mapped' => false,
                'class' => Substance::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medicine::class,
        ]);
    }
}
