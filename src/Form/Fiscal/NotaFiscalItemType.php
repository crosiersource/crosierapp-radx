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
            'required' => true
        ]);

        $builder->add('cfop', TextType::class, [
            'label' => 'CFOP',
            'required' => true
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

        $builder->add('icms_valor', MoneyType::class, [
            'label' => 'ICMS Valor',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => true,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('icms_valor_bc', MoneyType::class, [
            'label' => 'ICMS BC',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => true,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('ncm', TextType::class, [
            'label' => 'NCM',
            'required' => true
        ]);

        $builder->add('csosn', IntegerType::class, [
            'label' => 'CSOSN',
            'required' => true,
            'empty_data' => '103'
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