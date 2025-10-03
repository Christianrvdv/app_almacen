<?php

namespace App\Form;

use App\Entity\AjusteInventario;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjusteInventarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', ChoiceType::class, [
                'choices' => [
                    '📥 Entrada de Inventario' => 'entrada',
                    '📤 Salida de Inventario' => 'salida',
                ],
                'required' => true,
                'placeholder' => 'Seleccione el tipo de movimiento',
                'attr' => ['class' => 'form-select-modern']
            ])
            ->add('cantidad', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control-modern',
                    'min' => 1,
                    'placeholder' => 'Cantidad de unidades'
                ]
            ])
            ->add('fecha', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control-modern']
            ])
            ->add('motivo', TextType::class, [
                'attr' => [
                    'class' => 'form-control-modern',
                    'placeholder' => 'Motivo del ajuste...'
                ]
            ])
            ->add('usuario', TextType::class, [
                'attr' => [
                    'class' => 'form-control-modern',
                    'placeholder' => 'Nombre del usuario responsable'
                ]
            ])
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Seleccione un producto',
                'attr' => ['class' => 'form-select-modern']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AjusteInventario::class,
        ]);
    }
}
