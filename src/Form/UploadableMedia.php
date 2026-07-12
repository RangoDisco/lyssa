<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * This is a custom type for handling file upload for App\Entity\Media.
 */
class UploadableMedia extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('file', DropzoneType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Image(
                        maxSize: '2M',
                        mimeTypes: ['image/*'],
                        mimeTypesMessage: "Please upload a valid image"
                    )
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DropzoneType::class,
        ]);
    }
}
