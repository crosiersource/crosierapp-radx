<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\BandeiraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\BandeiraCartaoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CategoriaRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\OperadoraCartaoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MovimentacaoCaixaType.
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoCaixaTransacaoCartaoType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    /**
     * @required
     * @param EntityManagerInterface $doctrine
     */
    public function setDoctrine(EntityManagerInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Movimentacao $movimentacao */
            $movimentacao = $event->getData();
            $form = $event->getForm();

            $options = [];
            $options['sacado'] = 'default'; // só

            $this->doBuildForm($form, $options, $movimentacao);
        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $options = [];

                if ($event->getData()['sacado'] ?? false) {
                    $sacado = $event->getData()['sacado'];
                    $options['sacado']['choices'] = [$sacado => $sacado];
                }

                $this->doBuildForm($form, $options);
            }
        );
    }


    public function getBlockPrefix()
    {
        return 'movimentacao';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Movimentacao::class
        ]);
    }


    private function doBuildForm(FormInterface $form, ?array $options = null, ?Movimentacao $movimentacao = null): void
    {
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
        }

        $disabled = false;
        // Não permitir edição de 60 - TRANSFERÊNCIA ENTRE CARTEIRAS ou 61 - TRANSFERÊNCIA DE ENTRADA DE CAIXA
        if ($movimentacao->getId() && $movimentacao->tipoLancto &&
            in_array($movimentacao->tipoLancto->getCodigo(), [60, 61, 62], true)) {
            $disabled = true;
        }

        $form->add('id', IntegerType::class, [
            'label' => 'Id',
            'disabled' => true,
            'required' => false
        ]);


        $repoTipoLancto = $this->doctrine->getRepository(TipoLancto::class);
        $tiposLanctos = [$repoTipoLancto->find(62)];

        $form->add('tipoLancto', EntityType::class, [
            'label' => 'Tipo Lancto',
            'class' => TipoLancto::class,
            'empty_data' => $movimentacao->tipoLancto,
            'choices' => $tiposLanctos,
            'choice_label' => 'descricaoMontada',
            'required' => true,
            'disabled' => $movimentacao->getId() ? true : false
        ]);


        /** @var CategoriaRepository $repoCategoria */
        $repoCategoria = $this->doctrine->getRepository(Categoria::class);
        $rsCategorias = $repoCategoria->findByFiltersSimpl([['codigoSuper', 'EQ', 1]], ['codigoOrd' => 'ASC']);
        $categoriaChoices = [];
        $categoriaChoicesAttr = [];
        foreach ($rsCategorias as $categoria) {
            $categoriaChoices[$categoria->getId()] = $categoria;
            $arr = [
                'data-codigo' => $categoria->codigo,
                'data-codigo-super' => $categoria->codigoSuper,
                'selected' => $movimentacao->categoria && $movimentacao->categoria->getId() === $categoria->getId(),
            ];
            if ($categoria->getSubCategs()->count() > 0) {
                $arr['disabled'] = 'disabled';
            }
            $categoriaChoicesAttr[$categoria->getId()] = $arr;
        }


        $form->add('categoria', EntityType::class, [
            'label' => 'Categoria',
            'class' => Categoria::class,
            'choice_label' => 'descricaoMontadaTree',
            'choices' => $categoriaChoices,
            'choice_attr' => $categoriaChoicesAttr,
            'data' => $movimentacao->categoria,
            'empty_data' => $movimentacao->categoria,
            'attr' => [
                'class' => 'autoSelect2 focusOnReady'
            ],
            'required' => false
        ]);

        $form->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'required' => false,
            'disabled' => $disabled
        ]);

        $carteirasChoices = $this->doctrine->getRepository(Carteira::class)->findByFiltersSimpl([['caixa', 'EQ', true]]);
        $carteiraVal = $movimentacao->carteira;
        if (!$carteiraVal && count($carteirasChoices) === 1) {
            $carteiraVal = $carteirasChoices[0];
        }

        $form->add('carteira', EntityType::class, [
            'label' => 'Carteira',
            'class' => Carteira::class,
            'choice_label' => function (?Carteira $carteira) {
                return $carteira ? $carteira->getDescricaoMontada() : null;
            },
            'data' => $carteiraVal,
            'choices' => $carteirasChoices,
            'attr' => ['class' => 'autoSelect2'],
            'required' => false,
            'disabled' => $disabled
        ]);


        if (isset($options['sacado'])) {
            // no formato: "CPF/CNPJ - NOME"
            $choices = $options['sacado']['choices'] ?? null;
            $sacado = $movimentacao->sacado ?? null;
            if ($sacado) {
                $choices = [$sacado => $sacado];
            } else if (!$sacado && !$movimentacao->getId()) {
                $sacado = $choices ? current($choices) : null;
            }
            $form->add('sacado', ChoiceType::class, [
                'label' => 'Sacado',
                'required' => false,
                'choices' => $choices,
                'data' => $sacado,
                'attr' =>
                    [
                        'disabled' => $disabled
                    ]
            ]);
        }

        $repoModo = $this->doctrine->getRepository(Modo::class);
        $modos =
            [
                $repoModo->findOneBy(['codigo' => 9]), // RECEB. CARTÃO CRÉDITO
                $repoModo->findOneBy(['codigo' => 10]), // RECEB. CARTÃO DÉBITO
            ];

        $form->add('modo', EntityType::class, [
            'label' => 'Modo',
            'class' => Modo::class,
            'data' => $movimentacao->modo,
            'placeholder' => '...',
            'empty_data' => $movimentacao->modo,
            'choices' => $modos,
            'choice_label' => function (?Modo $modo) {
                return $modo ? $modo->getDescricaoMontada() : null;
            },
            'required' => false,
            'disabled' => $disabled
        ]);

        /** @var BandeiraCartaoRepository $repoBandeiraCartao */
        $repoBandeiraCartao = $this->doctrine->getRepository(BandeiraCartao::class);

        $form->add('bandeiraCartao', EntityType::class, [
            'label' => 'Bandeira',
            'class' => BandeiraCartao::class,
            'choices' => $repoBandeiraCartao->findAll(WhereBuilder::buildOrderBy('descricao')),
            'choice_label' => function (?BandeiraCartao $bandeiraCartao) {
                return $bandeiraCartao ? $bandeiraCartao->descricao : '';
            },
            'required' => false,
            'disabled' => $disabled
        ]);

        /** @var OperadoraCartaoRepository $repoOperadoraCartao */
        $repoOperadoraCartao = $this->doctrine->getRepository(OperadoraCartao::class);

        $operadoraChoices = $this->doctrine->getRepository(OperadoraCartao::class)->findAll();
        $operadoraVal = $movimentacao->operadoraCartao;
        if (!$operadoraVal && count($operadoraChoices) === 1) {
            $operadoraVal = $operadoraChoices[0];
        }

        $form->add('operadoraCartao', EntityType::class, [
            'label' => 'Operadora',
            'class' => OperadoraCartao::class,
            'choices' => $repoOperadoraCartao->findAll(WhereBuilder::buildOrderBy('descricao')),
            'choice_label' => function (?OperadoraCartao $operadoraCartao) {
                return $operadoraCartao ? $operadoraCartao->descricao : '';
            },
            'required' => false,
            'data' => $operadoraVal,
            'attr' => ['class' => 'autoSelect2'],
            'disabled' => $disabled
        ]);

        $form->add('qtdeParcelasCartao', IntegerType::class, [
            'label' => 'Qtde Parcelas',
            'required' => false,
            'disabled' => $disabled
        ]);

        $form->add('idTransacaoCartao', TextType::class, [
            'label' => 'ID Transação',
            'required' => false,
            'disabled' => $disabled
        ]);

        $form->add('numCartao', TextType::class, [
            'label' => 'Numeração Cartão',
            'required' => false,
            'disabled' => $disabled
        ]);


        $form->add('dtMoviment', DateType::class, [
            'label' => 'Dt Moviment',
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'crsr-date'
            ],
            'required' => false,
            'disabled' => $disabled
        ]);


        $form->add('obs', TextareaType::class, [
            'label' => 'Obs',
            'required' => false,
            'disabled' => $disabled
        ]);

        $form->add('valor', MoneyType::class, [
            'label' => 'Valor',
            'currency' => 'BRL',
            'grouping' => 'true',
            'attr' => [
                'class' => 'crsr-money',
                'style' => 'width: 150px'
            ],
            'required' => false,
            'disabled' => $disabled
        ]);
    }
}