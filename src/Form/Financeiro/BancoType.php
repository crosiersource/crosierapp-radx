<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Banco;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BancoType
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class BancoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('codigoBanco', IntegerType::class, [
            'label' => 'Código'
        ]);

        $builder->add('nome', TextType::class, [
            'label' => 'Descrição'
        ]);

        $builder->add('utilizado', ChoiceType::class, [
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Banco::class
        ]);
    }
}