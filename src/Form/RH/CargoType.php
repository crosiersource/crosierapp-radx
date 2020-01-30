<?php

namespace App\Form\RH;

use App\Entity\RH\Cargo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType para a entidade Cargo.
 *
 * @package App\Form
 * @author Carlos Eduardo Pauluk
 */
class CargoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('descricao', TextType::class, array(
            'label' => 'Descrição'
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Cargo::class
        ));
    }
}