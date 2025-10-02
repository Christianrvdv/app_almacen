<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Producto;
use App\Entity\Proveedor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del producto']
            ])
            ->add('descipcion', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción del producto']
            ])
            ->add('codigo_barras', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código de barras']
            ])
            ->add('precio_compra', NumberType::class, [
                'attr' => ['class' => 'form-control', 'step' => '0.01', 'min' => '0']
            ])
            ->add('precio_venta_actual', NumberType::class, [
                'attr' => ['class' => 'form-control', 'step' => '0.01', 'min' => '0']
            ])
            ->add('stock_minimo', NumberType::class, [
                'attr' => ['class' => 'form-control', 'min' => '0']
            ])
            ->add('activo', ChoiceType::class, [
                'choices' => [
                    'Activo' => true,
                    'Inactivo' => false,
                ],
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Selecciona un estado',
            ])
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Selecciona una categoría',
            ])
            ->add('proveedor', EntityType::class, [
                'class' => Proveedor::class,
                'choice_label' => 'nombre',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Selecciona un proveedor',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
