<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Banco;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\RegraImportacaoLinha;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegraImportacaoLinhaType
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class RegraImportacaoLinhaType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('regraRegexJava', TextType::class, [
            'label' => 'Regex Java'
        ]);

        $builder->add('tipoLancto', EntityType::class, [
            'label' => 'Tipo Lancto',
            'class' => TipoLancto::class,
            'choices' => $this->doctrine->getRepository(TipoLancto::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => 'descricaoMontada',
            'required' => true,
            'attr' => [
                'class' => 'autoSelect2 focusOnReady',
            ]
        ]);

        // Adiciono este por default, sabendo que será alterado no beforeSave
        $builder->add('status', HiddenType::class, [
            'label' => 'Status',
            'data' => 'REALIZADA',
            'required' => false
        ]);

        $builder->add('carteira', EntityType::class, [
            'label' => 'Carteira',
            'class' => Carteira::class,
            'choices' => $this->doctrine->getRepository(Carteira::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => function (?Carteira $carteira) {
                if ($carteira) {
                    return $carteira->getCodigo() . ' - ' . $carteira->getDescricao();
                }
                return null;
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('carteiraDestino', EntityType::class, [
            'label' => 'Carteira Destino',
            'class' => Carteira::class,
            'choices' => $this->doctrine->getRepository(Carteira::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => function (?Carteira $carteira) {
                if ($carteira) {
                    return $carteira->getCodigo() . ' - ' . $carteira->getDescricao();
                }
                return null;
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('centroCusto', EntityType::class, [
            'class' => CentroCusto::class,
            'choices' => $this->doctrine->getRepository(CentroCusto::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => function (CentroCusto $centroCusto) {
                return $centroCusto->getDescricao();
            },
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('modo', EntityType::class, [
            'class' => Modo::class,
            'choices' => $this->doctrine->getRepository(Modo::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => function (Modo $modo) {
                return $modo->getCodigo() . ' - ' . $modo->getDescricao();
            },
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('padraoDescricao', TextType::class, [
            'label' => 'Padrão da Descrição',
            'attr' => ['style' => 'text-transform: none;'],
            'data' => '%s'
        ]);

        $builder->add('categoria', EntityType::class, [
            'class' => Categoria::class,
            'choice_label' => 'descricaoMontada',
            'attr' => [
                'data-route' => 'categoria_select2json',
                'class' => 'autoSelect2'
            ]
        ]);

        $builder->add('sinalValor', ChoiceType::class, [
            'choices' => [
                'Ambos' => 0,
                'Positivo' => 1,
                'Negativo' => -1
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('chequeBanco', EntityType::class, [
            'required' => false,
            'label' => 'Cheque - Banco',
            'class' => Banco::class,
            'choices' => $this->doctrine->getRepository(Banco::class)->findAll(),
            'choice_label' => function (Banco $banco) {
                return sprintf('%03d', $banco->getCodigoBanco()) . ' - ' . $banco->getNome();
            },
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('chequeAgencia', TextType::class, [
            'label' => 'Cheque - Agência',
            'required' => false
        ]);

        $builder->add('chequeConta', TextType::class, [
            'label' => 'Cheque - Conta',
            'required' => false
        ]);

        $builder->add('chequeNumCheque', TextType::class, [
            'label' => 'Cheque - Número',
            'required' => false
        ]);


    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => RegraImportacaoLinha::class
        ));
    }
}