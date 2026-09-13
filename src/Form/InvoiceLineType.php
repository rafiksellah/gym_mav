<?php

namespace App\Form;

use App\Entity\InvoiceLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Désignation',
                'attr' => ['class' => 'js-line-description'],
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => false,
                'attr' => ['class' => 'js-line-reference'],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Qté',
                'html5' => true,
                'scale' => 3,
                'attr' => ['step' => '0.001', 'min' => 0, 'class' => 'js-line-qty'],
            ])
            ->add('unit', TextType::class, [
                'label' => 'Unité',
                'required' => false,
                'attr' => ['class' => 'js-line-unit'],
            ])
            ->add('unitPriceHt', NumberType::class, [
                'label' => 'P.U HT',
                'html5' => true,
                'scale' => 2,
                'attr' => ['step' => '0.01', 'min' => 0, 'class' => 'js-line-price'],
            ])
            ->add('discountPercent', NumberType::class, [
                'label' => 'Remise %',
                'html5' => true,
                'scale' => 2,
                'attr' => ['step' => '0.01', 'min' => 0, 'max' => 100, 'class' => 'js-line-discount'],
            ])
            ->add('vatRate', NumberType::class, [
                'label' => 'TVA %',
                'html5' => true,
                'scale' => 2,
                'attr' => ['step' => '0.01', 'min' => 0, 'max' => 100, 'class' => 'js-line-vat'],
            ])
            ->add('position', HiddenType::class, [
                'attr' => ['class' => 'js-line-position'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoiceLine::class,
        ]);
    }
}
