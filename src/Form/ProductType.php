<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = $options['is_new'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'query_builder' => fn (CategoryRepository $repo) => $repo->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => ['placeholder' => 'Phrase accrocheuse affichée dans les listes'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description complète',
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('brand', TextType::class, [
                'label' => 'Marque',
                'required' => false,
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => false,
            ])
            ->add('keyFeatures', TextareaType::class, [
                'label' => 'Caractéristiques principales',
                'required' => false,
                'attr' => ['rows' => 6, 'placeholder' => "Une caractéristique par ligne"],
                'help' => 'Une caractéristique par ligne. Affichée en checklist sur la fiche produit.',
            ])
            ->add('technicalSpecs', TextareaType::class, [
                'label' => 'Spécifications techniques',
                'required' => false,
                'attr' => ['rows' => 6, 'placeholder' => "Dimensions: 360 x 110 x 220 cm\nPoids net: 540 kg"],
                'help' => 'Une spécification par ligne, au format "Label: Valeur". Affichée en tableau sur la fiche produit.',
            ])
            ->add('mainImageFile', FileType::class, [
                'label' => $isNew ? 'Image principale' : 'Remplacer l\'image principale',
                'mapped' => false,
                'required' => $isNew,
                'constraints' => [
                    new File(
                        maxSize: '4M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Merci de déposer une image valide (JPEG, PNG ou WebP).',
                    ),
                ],
            ])
            ->add('galleryFiles', FileType::class, [
                'label' => 'Images de la galerie',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'help' => 'Vous pouvez sélectionner plusieurs images.',
                'constraints' => [
                    new All([
                        new File(
                            maxSize: '4M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                            mimeTypesMessage: 'Merci de déposer des images valides (JPEG, PNG ou WebP).',
                        ),
                    ]),
                ],
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Mettre en avant sur la page d\'accueil',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Produit actif (visible sur le site)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
        $resolver->setRequired('is_new');
    }
}
