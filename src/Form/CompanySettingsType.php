<?php

namespace App\Form;

use App\Entity\CompanySettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CompanySettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom de l'entreprise",
            ])
            ->add('tagline', TextType::class, [
                'label' => 'Slogan / activité',
                'required' => false,
            ])
            ->add('slogan', TextType::class, [
                'label' => "Phrase d'accroche (style script)",
                'required' => false,
                'help' => 'Affichée en italique sous le slogan, ex : "Votre partenaire pour une meilleure performance".',
            ])
            ->add('logoFile', FileType::class, [
                'label' => 'Logo',
                'mapped' => false,
                'required' => false,
                'help' => 'Image utilisée en en-tête des factures PDF (PNG ou JPEG, fond transparent conseillé).',
                'constraints' => [
                    new File(
                        maxSize: '4M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'],
                        mimeTypesMessage: 'Merci de déposer une image valide (JPEG, PNG, WebP ou SVG).',
                    ),
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['rows' => 2],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('website', TextType::class, [
                'label' => 'Site web',
                'required' => false,
            ])
            ->add('nif', TextType::class, [
                'label' => 'NIF',
                'required' => false,
            ])
            ->add('nis', TextType::class, [
                'label' => 'NIS',
                'required' => false,
            ])
            ->add('rc', TextType::class, [
                'label' => 'RC',
                'required' => false,
            ])
            ->add('ai', TextType::class, [
                'label' => 'AI',
                'required' => false,
            ])
            ->add('rib', TextType::class, [
                'label' => 'RIB',
                'required' => false,
            ])
            ->add('capitalSocial', TextType::class, [
                'label' => 'Capital social',
                'required' => false,
            ])
            ->add('bankInfo', TextareaType::class, [
                'label' => 'Informations bancaires',
                'required' => false,
                'attr' => ['rows' => 2, 'placeholder' => 'Ex : CPA - Agence 416 Khemisli (01 Boulevard Mohamed Khemisti - ORAN 31000).'],
            ])
            ->add('paymentTerms', TextareaType::class, [
                'label' => 'Conditions de paiement',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Affichées en pied de facture.',
            ])
            ->add('legalMentions', TextareaType::class, [
                'label' => 'Mentions légales',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('defaultVatRate', NumberType::class, [
                'label' => 'TVA par défaut (%)',
                'html5' => true,
                'attr' => ['step' => '0.01', 'min' => 0, 'max' => 100],
            ])
            ->add('defaultCurrency', TextType::class, [
                'label' => 'Devise par défaut',
                'attr' => ['maxlength' => 10],
            ])
            ->add('invoiceNumberPrefix', TextType::class, [
                'label' => 'Préfixe de numérotation',
                'required' => false,
                'help' => 'Ex : "SP" donne des numéros du type 58/SP/26.',
                'attr' => ['maxlength' => 20],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompanySettings::class,
        ]);
    }
}
