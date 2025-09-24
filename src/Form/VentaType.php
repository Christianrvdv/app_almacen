<?php

namespace App\Form;

use App\Entity\Cliente;
use App\Entity\Venta;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fecha')
            ->add('total')
            ->add('tipo_veenta')
            ->add('estado')
            ->add('cliente', EntityType::class, [
                'class' => Cliente::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Sin definir',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venta::class,
        ]);
    }
}
