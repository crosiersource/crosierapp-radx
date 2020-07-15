<?php

namespace App\Form\Estoque;

use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UnidadeType
 *
 * @author Carlos Eduardo Pauluk
 */
class UnidadeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id', IntegerType::class, array(
            'label' => 'Id',
            'required' => false
        ));

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'attr' => [
                'maxlength' => '255'
            ]
        ]);

        $builder->add('label', TextType::class, [
            'label' => 'Label',
            'attr' => [
                'maxlength' => '10',
                'class' => 'notuppercase',
            ]
        ]);

        $builder->add('casasDecimais', IntegerType::class, [
            'label' => 'Casas Decimais',
        ]);

        $builder->add('atual', ChoiceType::class, [
            'label' => 'Atual',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2'],
        ]);

        $builder->add('jsonInfo', TextType::class, [
            'label' => 'JSON Info',
            'attr' => [
                'class' => 'json'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Unidade::class
        ));
    }
}