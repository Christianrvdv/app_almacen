<?php

namespace App\Form;

use App\Entity\AjusteInventario;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjusteInventarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo')
            ->add('cantidad')
            ->add('fecha')
            ->add('motivo')
            ->add('usuario')
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'id',
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
