<?php

namespace App\Form\Estoque;

use App\Entity\Estoque\Subgrupo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class SubgrupoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('UUID', TextType::class, [
            'label' => 'UUID',
            'attr' => ['readonly' => true, 'class' => 'notuppercase'],
            'required' => true
        ]);

        $builder->add('codigo', TextType::class, [
            'label' => 'Código',
            'attr' => ['class' => 'focusOnReady'],
        ]);

        $builder->add('nome', TextType::class, [
            'label' => 'Nome'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Subgrupo::class
        ));
    }
}