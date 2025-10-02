<?php

namespace App\Form;

use App\Entity\AjusteInventario;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjusteInventarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', ChoiceType::class, [
                'choices' => [
                    'Entrada' => 'entrada',
                    'Salida' => 'salida',
                ],
                'required' => true,
                'placeholder' => 'Seleccione una opcion',
            ])
            ->add('cantidad')
            ->add('fecha')
            ->add('motivo')
            ->add('usuario')
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'nombre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AjusteInventario::class,
        ]);
    }
}
