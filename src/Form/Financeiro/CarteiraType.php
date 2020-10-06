<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Banco;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CarteiraType
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CarteiraType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('codigo', IntegerType::class, [
            'label' => 'Código',
            'attr' => ['class' => 'focusOnReady']
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição'
        ]);

        $builder->add('dtConsolidado', DateType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'label' => 'Dt Consolidado',
            'attr' => ['class' => 'crsr-date'],
            'help' => 'Trava lançamentos para datas anteriores'
        ]);

        $builder->add('concreta', ChoiceType::class, [
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('abertas', ChoiceType::class, [
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('caixa', ChoiceType::class, [
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('cheque', ChoiceType::class, [
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('banco', EntityType::class, [
            // looks for choices from this entity
            'class' => Banco::class,
            // 'choices' => $this->doctrine->getRepository(Banco::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'placeholder' => '...',
            'required' => false,
            // uses the User.username property as the visible option string
            'choice_label' => function (Banco $banco) {
                return $banco->getCodigoBanco() . ' - ' . $banco->getNome();
            },
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('agencia', TextType::class, [
            'label' => 'Agência',
            'required' => false
        ]);

        $builder->add('conta', TextType::class, [
            'label' => 'Conta',
            'required' => false
        ]);

        $builder->add('limite', MoneyType::class, [
            'label' => 'Limite',
            'currency' => 'BRL',
            'grouping' => 'true',
            'required' => false,
            'attr' => [
                'class' => 'crsr-money'
            ]
        ]);

        $builder->add('operadoraCartao', EntityType::class, [
            'class' => OperadoraCartao::class,
            'placeholder' => '...',
            'required' => false,
            // uses the User.username property as the visible option string
            'choice_label' => 'descricao',
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('atual', ChoiceType::class, [
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Carteira::class
        ));
    }
}