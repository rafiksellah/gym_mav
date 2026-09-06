<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4],
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône Bootstrap Icons',
                'attr' => ['placeholder' => 'bi-tools'],
                'help' => 'Nom de classe depuis <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">icons.getbootstrap.com</a>, ex : bi-tools, bi-truck, bi-headset.',
                'help_html' => true,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (facultative)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '4M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Merci de déposer une image valide (JPEG, PNG ou WebP).',
                    ),
                ],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'help' => 'Les services sont affichés du plus petit au plus grand.',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Service actif (visible sur le site)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
