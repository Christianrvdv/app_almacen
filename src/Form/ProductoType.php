<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Producto;
use App\Entity\Proveedor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('descipcion')
            ->add('codigo_barras')
            ->add('precio_compra')
            ->add('precio_venta_actual')
            ->add('stock_minimo')
            ->add('activo', ChoiceType::class, [
                'choices' => [
                    'Activo' => '1',
                    'Inactivo' => '0',
                ],
                'required' => true,
                'placeholder' => 'Selecciona un estado',
            ])
            ->add('fecha_creaccion')
            ->add('fecha_actualizacion')
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Sin definir',
            ])
            ->add('proveedor', EntityType::class, [
                'class' => Proveedor::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Sin definir',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
