<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibRadxBundle\Business\Financeiro\MovimentacaoBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Banco;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\BandeiraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\GrupoItem;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CarteiraRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class MovimentacaoTypeBuilder
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoTypeBuilder
{

    private EntityManagerInterface $doctrine;

    public MovimentacaoBusiness $movimentacaoBusiness;

    private Security $security;


    /**
     * @required
     * @param EntityManagerInterface $doctrine
     */
    public function setDoctrine(EntityManagerInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @required
     * @param MovimentacaoBusiness $movimentacaoBusiness
     */
    public function setMovimentacaoBusiness(MovimentacaoBusiness $movimentacaoBusiness): void
    {
        $this->movimentacaoBusiness = $movimentacaoBusiness;
    }


    /**
     * @required
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /**
     * @param FormInterface $form
     * @param Movimentacao|null $movimentacao
     * @param array $options
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function build(FormInterface $form, ?Movimentacao $movimentacao = null, array $options = [])
    {
        // $builder = $event->getForm();
        if (!$movimentacao) {
            $movimentacao = new Movimentacao();
        }

        $form->add('id', IntegerType::class, [
            'label' => 'Id',
            'disabled' => true,
            'required' => false
        ]);

        $form->add('tipoLancto', EntityType::class, [
            'label' => 'Tipo Lancto',
            'class' => TipoLancto::class,
            'empty_data' => $movimentacao->tipoLancto,
            'placeholder' => '...',
            'choices' => $this->doctrine->getRepository(TipoLancto::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => 'descricaoMontada',
            'required' => true,
            'attr' => [
                'class' => 'autoSelect2',
                'data-val' => $movimentacao->tipoLancto ? $movimentacao->tipoLancto->getId() : null
            ]
        ]);

        /** @var CategoriaRepository $repoCategoria */
        $repoCategoria = $this->doctrine->getRepository(Categoria::class);
        $rsCategorias = $repoCategoria->findAll(['codigoOrd' => 'ASC']);
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
                'data-val' => (null !== $movimentacao and null !== $movimentacao->categoria and null !== $movimentacao->categoria->getId()) ? $movimentacao->categoria->getId() : '',
            ],
            'required' => false
        ]);

        $form->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'required' => false,
            'attr' => [
                'style' => 'background-color: lightgoldenrodyellow'
            ],
        ]);

        $form->add('UUID', TextType::class, [
            'label' => 'UUID',
            'disabled' => true,
            'required' => false
        ]);


        $form->add('documentoBanco', EntityType::class, [
            'label' => 'Banco (Documento)',
            'help' => 'Banco emissor do boleto',
            'class' => Banco::class,
            'choices' => $this->doctrine->getRepository(Banco::class)
                ->findAll(WhereBuilder::buildOrderBy('codigoBanco')),
            'choice_label' => function (Banco $banco) {
                return sprintf('%03d', $banco->getCodigoBanco()) . ' - ' . $banco->nome;
            },
            'required' => false,
            'placeholder' => '...',
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('documentoNum', TextType::class,
            [
                'label' => 'Núm Documento',
                'required' => false,
            ]
        );


        if (isset($options['sacado'])) {
            // no formato: "CPF/CNPJ - NOME"
            $choices = $options['sacado']['choices'] ?? null;
            $sacado = $movimentacao->sacado ?? null;
            if (!$sacado && !$movimentacao->getId()) {
                $sacado = $choices ? current($choices) : null;
            }
            $form->add('sacado', ChoiceType::class, [
                'label' => 'Sacado',
                'help' => 'Quem paga o valor do título',
                'required' => false,
                'choices' => $choices,
                'data' => $sacado,
                'attr' =>
                    [
//                        'data-val' => $sacado,
                        'disabled' => $choices === null
                    ]
            ]);
        }

        if (isset($options['cedente'])) {
            // no formato: "CPF/CNPJ - NOME"
            $choices = $options['cedente']['choices'] ?? null;
            $cedente = $movimentacao->cedente ?? null;
            if (!$cedente && !$movimentacao->getId()) {
                $cedente = $choices ? current($choices) : null;
            }
            $form->add('cedente', ChoiceType::class, [
                'label' => 'Cedente',
                'help' => 'Quem recebe o valor do título',
                'required' => false,
                'choices' => $choices,
                'data' => $cedente,
                'attr' =>
                    [
//                        'data-val' => $cedente,
                        'disabled' => $choices === null
                    ]
            ]);
        }


        // Adiciono este por default, sabendo que será alterado no beforeSave
        $form->add('status', HiddenType::class, [
            'label' => 'Status',
            'data' => 'ABERTA',
            'required' => false
        ]);

        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->doctrine->getRepository(Carteira::class);

        $carteiraChoices =
            $options['carteiras'] ??
            $repoCarteira->findByFiltersSimpl([['atual', 'EQ', true]], ['e.codigo' => 'ASC'], 0, -1);


        $form->add('carteira', EntityType::class, [
            'label' => 'Carteira',
            'class' => Carteira::class,
            'choice_label' => function (?Carteira $carteira) {
                return $carteira ? $carteira->getDescricaoMontada() : null;
            },
            'choices' => $carteiraChoices,
            'attr' => [
                'class' => 'autoSelect2'
            ],
            'required' => false
        ]);

        // só é obrigatório nos casos de tipoLancto 60 "TRANSFERÊNCIA ENTRE CARTEIRAS" e 61 "TRANSFERÊNCIA DE ENTRADA DE CAIXA"
        $carteiraDestinoChoices =
            $options['carteirasDestino'] ??
            $repoCarteira->findByFiltersSimpl([['atual', 'EQ', true]], ['e.codigo' => 'ASC'], 0, -1);
        $form->add('carteiraDestino', EntityType::class, [
            'label' => 'Destino',
            'class' => Carteira::class,
            'choice_label' => 'descricaoMontada',
            'choices' => $carteiraDestinoChoices,
            'attr' => [
                'data-val' => (null !== $movimentacao and null !== $movimentacao->carteiraDestino) ? $movimentacao->carteiraDestino->getId() : '',
                'class' => 'autoSelect2'
            ],
            'required' => false
        ]);

        // Também passo o data-valpai para poder selecionar o valor no campo 'grupo' que é somente um auxiliar na tela
        $form->add('grupoItem', EntityType::class, [
            'label' => 'Grupo',
            'class' => GrupoItem::class,
            'choice_label' => 'descricao',
            'choices' => null,
            'data' => (null !== $movimentacao and null !== $movimentacao->grupoItem) ? $movimentacao->grupoItem : null,
            'required' => false,
            'disabled' => true
        ]);


        $modoChoices = $options['modos'] ?? $this->doctrine->getRepository(Modo::class)->findAll(['codigo' => 'ASC']);

        $form->add('modo', EntityType::class, [
            'label' => 'Modo',
            'class' => Modo::class,
            'data' => $movimentacao->modo,
            'placeholder' => '...',
            'choices' => $modoChoices,
            'empty_data' => 0,
            'choice_label' => function (?Modo $modo) {
                return $modo ? $modo->getDescricaoMontada() : null;
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2 focusOnReady']
        ]);

        $form->add('bandeiraCartao', EntityType::class, [
            'label' => 'Bandeira',
            'class' => BandeiraCartao::class,
            'choices' => $this->doctrine->getRepository(BandeiraCartao::class)->findAll(WhereBuilder::buildOrderBy('descricao')),
            'choice_label' => function (?BandeiraCartao $bandeiraCartao) {
                return $bandeiraCartao ? $bandeiraCartao->descricao : '';
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('operadoraCartao', EntityType::class, [
            'label' => 'Operadora',
            'class' => OperadoraCartao::class,
            'choices' => $this->doctrine->getRepository(OperadoraCartao::class)->findAll(WhereBuilder::buildOrderBy('descricao')),
            'choice_label' => function (?OperadoraCartao $operadoraCartao) {
                return $operadoraCartao ? $operadoraCartao->descricao : '';
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('centroCusto', EntityType::class, [
            'label' => 'Centro de Custo',
            'class' => CentroCusto::class,
            'data' => $movimentacao->centroCusto,
            'empty_data' => $movimentacao->centroCusto,
            'choices' => $this->doctrine->getRepository(CentroCusto::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => 'descricaoMontada',
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);


        $form->add('dtMoviment', DateType::class, [
            'label' => 'Dt Moviment',
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'crsr-date'
            ],
            'required' => false
        ]);

        $form->add('dtVencto', DateType::class, [
            'label' => 'Dt Vencto',
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'crsr-date'
            ],
            'required' => false
        ]);

        $form->add('dtVenctoEfetiva', DateType::class, [
            'label' => 'Dt Vencto Efet',
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'crsr-date',
                'data-route' => '/base/diaUtil/findDiaUtil'
            ],
            'required' => false
        ]);

        $form->add('dtPagto', DateType::class, [
            'label' => 'Dt Pagto',
            'widget' => 'single_text',
            'required' => false,
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'crsr-date'
            ]
        ]);

        $form->add('obs', TextareaType::class, [
            'label' => 'Obs',
            'required' => false
        ]);

        $form->add('valor', MoneyType::class, [
            'label' => 'Valor',
            'currency' => 'BRL',
            'grouping' => 'true',
            'attr' => [
                'class' => 'crsr-money'
            ],
            'required' => false
        ]);

        $form->add('descontos', MoneyType::class, [
            'label' => 'Descontos',
            'currency' => 'BRL',
            'grouping' => 'true',
            'attr' => [
                'class' => 'crsr-money'
            ],
            'required' => false
        ]);

        $form->add('acrescimos', MoneyType::class, [
            'label' => 'Acréscimos',
            'currency' => 'BRL',
            'grouping' => 'true',
            'attr' => [
                'class' => 'crsr-money'
            ],
            'required' => false
        ]);

        $form->add('valorTotal', MoneyType::class, [
            'label' => 'Valor Total',
            'currency' => 'BRL',
            'grouping' => 'true',
            'attr' => [
                'class' => 'crsr-money'
            ],
            'disabled' => true,
            'required' => false
        ]);

        // Cheque

        $form->add('chequeBanco', EntityType::class, [
            'label' => 'Banco',
            'class' => Banco::class,
            'choices' => $this->doctrine->getRepository(Banco::class)
                ->findAll(WhereBuilder::buildOrderBy('codigoBanco')),
            'choice_label' => function (Banco $banco) {
                return sprintf('%03d', $banco->getCodigoBanco()) . ' - ' . $banco->nome;
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('chequeAgencia', TextType::class, [
            'label' => 'Agência',
            'required' => false
        ]);

        $form->add('chequeConta', TextType::class, [
            'label' => 'Conta',
            'required' => false
        ]);

        $form->add('chequeNumCheque', TextType::class, [
            'label' => 'Núm Cheque',
            'required' => false
        ]);

        // Recorrência


        $form->add('recorrente', ChoiceType::class, [
            'label' => 'Recorrente',
            'choices' => [
                'SIM' => true,
                'NÃO' => false
            ],
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('recorrDia', IntegerType::class, [
            'label' => 'Dia',
            'attr' => ['min' => 1, 'max' => 32],
            'required' => false
        ]);

        $form->add('recorrTipoRepet', ChoiceType::class, [
            'label' => 'Tipo Repet',
            'choices' => [
                'DIA FIXO' => 'DIA_FIXO',
                'DIA ÚTIL' => 'DIA_UTIL'
            ],
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('recorrFrequencia', ChoiceType::class, [
            'label' => 'Frequência',
            'choices' => [
                'MENSAL' => 'MENSAL',
                'ANUAL' => 'ANUAL'
            ],
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('recorrVariacao', IntegerType::class, [
            'label' => 'Variação',
            'required' => false
        ]);


        $form->add('quitado', ChoiceType::class, [
            'label' => 'Quitado',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ]
        ]);

        $form->add('parcelamento', ChoiceType::class, [
            'label' => 'Parcelamento',
            'choices' => [
                'Sim' => true,
                'Não' => false
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

    }

}