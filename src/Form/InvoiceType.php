<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Repository\ClientRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Numéro de facture',
            ])
            ->add('invoiceDate', DateType::class, [
                'label' => 'Date de facture',
                'widget' => 'single_text',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'fullName',
                'label' => 'Client',
                'placeholder' => 'Choisir un client',
                'query_builder' => fn (ClientRepository $repo) => $repo->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
            ])
            ->add('currency', TextType::class, [
                'label' => 'Devise',
                'attr' => ['maxlength' => 10],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes / conditions particulières',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('lines', CollectionType::class, [
                'entry_type' => InvoiceLineType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__line__',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
