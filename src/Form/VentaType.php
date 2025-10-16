<?php

namespace App\Form;

use App\Entity\Cliente;
use App\Entity\Venta;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fecha')
            ->add('total')
            ->add('tipo_veenta', ChoiceType::class, [
                'choices' => [
                    'Efectivo' => 'efectivo',
                    'Transferencia' => 'transferencia',
                    'Mixto' => 'mixto',
                ],
                'required' => true,
                'placeholder' => 'Selecciona método de pago',
            ])
            ->add('estado', ChoiceType::class, [
                'choices' => [
                    'Completada' => 'completada',
                    'Pendiente' => 'pendiente',
                ],
                'required' => true,
                'placeholder' => 'Selecciona un estado',
            ])
            ->add('cliente', EntityType::class, [
                'class' => Cliente::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Sin definir',
            ])
            ->add('detalleVentas', CollectionType::class, [
                'entry_type' => DetalleVentaType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venta::class,
        ]);
    }
}
