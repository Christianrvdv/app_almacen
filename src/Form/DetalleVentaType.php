<?php

namespace App\Form;

use App\Entity\DetalleVenta;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetalleVentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad', NumberType::class, [
                'label' => 'Cantidad',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'step' => 1
                ],
                'html5' => true
            ])
            ->add('precio_unitario', NumberType::class, [
                'label' => 'Precio Unitario',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0.01,
                    'step' => 0.01
                ],
                'html5' => true,
                'scale' => 2
            ])
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Seleccione un producto',
                'attr' => ['class' => 'form-select'],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetalleVenta::class,
        ]);
    }
}
