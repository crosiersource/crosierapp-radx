<?php

namespace App\Form\Fiscal;

use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalItemType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('codigo', TextType::class, [
            'label' => 'Código',
            'required' => true,
            'attr' => ['class' => 'focusOnReady']
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'required' => true,
            'attr' => [
                'maxlength' => '120'
            ]
        ]);

        $builder->add('cfop', TextType::class, [
            'label' => 'CFOP',
            'required' => true
        ]);

        $builder->add('csosn', IntegerType::class, [
            'label' => 'CSOSN',
            'required' => false,
        ]);

        $builder->add('ncm', TextType::class, [
            'label' => 'NCM',
            'required' => true
        ]);

        $builder->add('cest', TextType::class, [
            'label' => 'CEST',
            'required' => false,
        ]);

        $builder->add('cst', TextType::class, [
            'label' => 'CST',
            'required' => false,
        ]);

        $builder->add('qtde', NumberType::class, [
            'label' => 'Qtde',
            'grouping' => 'true',
            'scale' => 3,
            'attr' => [
                'class' => 'crsr-dec3'
            ],
            'required' => true
        ]);

        $builder->add('unidade', TextType::class, [
            'label' => 'Unidade',
            'required' => true
        ]);


        $builder->add('icmsValor', MoneyType::class, [
            'label' => 'ICMS Valor',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('icmsModBC', TextType::class, [
            'label' => 'ICMS Mód BC',
            'required' => false
        ]);

        $builder->add('icmsAliquota', NumberType::class, [
            'label' => 'ICMS Aliq',
            'scale' => 2,
            'help' => 'Em %',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-dec2'
            ]
        ]);

        $builder->add('icmsValorBc', MoneyType::class, [
            'label' => 'ICMS BC',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);



        $builder->add('pisValor', MoneyType::class, [
            'label' => 'PIS Valor',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('pisAliquota', NumberType::class, [
            'label' => 'PIS Aliq',
            'scale' => 2,
            'help' => 'Em %',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-dec2'
            ]
        ]);

        $builder->add('pisValorBc', MoneyType::class, [
            'label' => 'PIS BC',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);


        $builder->add('cofinsValor', MoneyType::class, [
            'label' => 'COFINS Valor',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('cofinsAliquota', NumberType::class, [
            'label' => 'COFINS Aliq',
            'scale' => 2,
            'help' => 'Em %',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-dec2'
            ]
        ]);

        $builder->add('cofinsValorBc', MoneyType::class, [
            'label' => 'COFINS BC',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);





        $builder->add('valor_unit', MoneyType::class, [
            'label' => 'Valor Unit',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => true,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('sub_total', MoneyType::class, [
            'label' => 'Subtotal',
            'currency' => 'BRL',
            'grouping' => 'true',
            'attr' => [
                'class' => 'crsr-money'
            ],
            'disabled' => true
        ]);

        $builder->add('valor_desconto', MoneyType::class, [
            'label' => 'Valor Desconto',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('valor_total', MoneyType::class, [
            'label' => 'Valor Total',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ],
            'disabled' => true
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NotaFiscalItem::class
        ]);
    }
}