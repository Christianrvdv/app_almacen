<?php

namespace App\Form;

use App\Entity\Cliente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ClienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre Completo',
                'attr' => [
                    'placeholder' => 'Ej: Juan Pérez García'
                ]
            ])
            ->add('telefono', TextType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: +34 612 345 678'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo Electrónico',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: juan.perez@email.com'
                ]
            ])
            ->add('direccion', TextType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: Calle Principal 123, Ciudad, País'
                ]
            ])
            ->add('compra_totales', NumberType::class, [
                'label' => 'Compras Totales ($)',
                'required' => false,
                'attr' => [
                    'placeholder' => '0.00'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cliente::class,
        ]);
    }
}
