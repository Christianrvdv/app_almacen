<?php

namespace App\Form;

use App\Entity\HistorialPrecios;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistorialPreciosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', ChoiceType::class, [
                'choices' => [
                    'Compra' => 'compra',
                    'Venta' => 'venta',
                ],
                'required' => true,
                'placeholder' => 'Seleccione una opción',
            ])
            ->add('precio_anterior')
            ->add('precio_nuevo')
            ->add('fecha_cambio')
            ->add('motivo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HistorialPrecios::class,
        ]);
    }
}
