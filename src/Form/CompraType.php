<?php

namespace App\Form;

use App\Entity\Compra;
use App\Entity\Proveedor;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CompraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fecha')
            ->add('numero_factura')
            ->add('total')
            ->add('estado', ChoiceType::class, [
                'choices' => [
                    'Pagado' => 'Pagado',
                    'Pendiente' => 'Pendiente',
                ],
                'required' => true,
                'placeholder' => 'Selecciona un estado',
            ])
            ->add('observaciones')
            ->add('proveedor', EntityType::class, [
                'class' => Proveedor::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Sin definir',
            ])
            ->add('detalleCompras', CollectionType::class, [
                'entry_type' => DetalleCompraType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compra::class,
        ]);
    }
}
