<?php

namespace App\Form;

use App\Entity\DetalleVenta;
use App\Entity\Producto;
use App\Entity\Venta;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetalleVentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad')
            ->add('precio_unitario')
            ->add('precio_costo')
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetalleVenta::class,
        ]);
    }
}
