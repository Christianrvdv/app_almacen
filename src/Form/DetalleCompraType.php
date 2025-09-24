<?php

namespace App\Form;

use App\Entity\Compra;
use App\Entity\DetalleCompra;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetalleCompraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad', NumberType::class, [
                'label' => 'Cantidad',
                'attr' => ['class' => 'form-control']
            ])
            ->add('precioUnitario', NumberType::class, [
                'label' => 'Precio Unitario',
                'attr' => ['class' => 'form-control']
            ])
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Seleccione un producto',
                'attr' => ['class' => 'form-select']
            ])  ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetalleCompra::class,
        ]);
    }
}
