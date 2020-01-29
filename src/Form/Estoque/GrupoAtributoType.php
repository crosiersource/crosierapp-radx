<?php

namespace App\Form\Estoque;

use App\Entity\Estoque\GrupoAtributo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class GrupoAtributoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('UUID', TextType::class, [
            'label' => 'UUID',
            'attr' => ['readonly' => true, 'class' => 'notuppercase'],
            'required' => true
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'attr' => ['class' => 'focusOnReady'],
        ]);

        $builder->add('label', TextType::class, [
            'label' => 'Label',
            'attr' => ['class' => 'notuppercase'],
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => GrupoAtributo::class
        ));
    }
}