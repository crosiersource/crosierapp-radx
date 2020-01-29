<?php

namespace App\Form\Financeiro;

use App\Entity\Financeiro\Modo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ModoType
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class ModoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('codigo', IntegerType::class, array(
            'label' => 'Código'
        ));

        $builder->add('descricao', TextType::class, array(
            'label' => 'Descrição'
        ));

        $builder->add('modoDeTransfPropria', ChoiceType::class, array(
            'label' => 'Transf Própria',
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));


        $builder->add('modoDeMovimentAgrup', ChoiceType::class, array(
            'label' => 'Moviment Agrup',
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));

        $builder->add('modoDeCartao', ChoiceType::class, array(
            'label' => 'Cartão',
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));

        $builder->add('modoDeCheque', ChoiceType::class, array(
            'label' => 'Cheque',
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));

        $builder->add('modoDeTransfCaixa', ChoiceType::class, array(
            'label' => 'Transf Caixa',
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));

        $builder->add('modoComBancoOrigem', ChoiceType::class, array(
            'label' => 'Com Banco Origem',
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Modo::class
        ));
    }
}