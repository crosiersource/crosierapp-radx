<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
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
        $builder->add('codigo', IntegerType::class, [
            'label' => 'Código'
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição'
        ]);

        $builder->add('modoDeTransfPropria', ChoiceType::class, [
            'label' => 'Transf Própria',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);

        $builder->add('modoDeMovimentAgrup', ChoiceType::class, [
            'label' => 'Moviment Agrup',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);

        $builder->add('modoDeCartao', ChoiceType::class, [
            'label' => 'Cartão',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);

        $builder->add('modoDeCheque', ChoiceType::class, [
            'label' => 'Cheque',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);

        $builder->add('modoDeTransfCaixa', ChoiceType::class, [
            'label' => 'Transf Caixa',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);

        $builder->add('modoComBancoOrigem', ChoiceType::class, [
            'label' => 'Com Banco Origem',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Modo::class
        ]);
    }
}