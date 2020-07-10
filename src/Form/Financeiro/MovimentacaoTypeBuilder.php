<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\PessoaRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class MovimentacaoTypeBuilder
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoTypeBuilder
{

    /** @var EntityManagerInterface */
    private $doctrine;

    /** @var Security */
    private $security;

    /** @var UrlGeneratorInterface */
    private $router;


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
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /**
     * @required
     * @param UrlGeneratorInterface $router
     */
    public function setRouter(UrlGeneratorInterface $router): void
    {
        $this->router = $router;
    }


    /**
     * @param FormInterface $form
     * @param Movimentacao|null $movimentacao
     * @param array $choices
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function build(FormInterface $form, ?Movimentacao $movimentacao = null, array $choices = [])
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
            'empty_data' => $movimentacao->getTipoLancto(),
            'placeholder' => '...',
            'choices' => $this->doctrine->getRepository(TipoLancto::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
            'choice_label' => 'descricaoMontada',
            'required' => true,
            'attr' => [
                'class' => 'autoSelect2',
                'data-val' => $movimentacao->getTipoLancto() ? $movimentacao->getTipoLancto()->getId() : null
            ]
        ]);

        /** @var CategoriaRepository $repoCategoria */
        $repoCategoria = $this->doctrine->getRepository(Categoria::class);
        $categorias = $repoCategoria->findAll(['codigoOrd' => 'ASC']);

        $form->add('categoria', EntityType::class, [
            'label' => 'Categoria',
            'class' => Categoria::class,
            'choice_label' => 'descricaoMontadaTree',
            'choices' => $categorias,
            'data' => $movimentacao->getCategoria(),
            'empty_data' => $movimentacao->getCategoria(),
            'attr' => [
                'data-val' => (null !== $movimentacao and null !== $movimentacao->getCategoria() and null !== $movimentacao->getCategoria()->getId()) ? $movimentacao->getCategoria()->getId() : '',
                'class' => 'autoSelect2'
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

        $form->add('uuid', TextType::class, [
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
                return sprintf('%03d', $banco->getCodigoBanco()) . ' - ' . $banco->getNome();
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

        /** @var PessoaRepository $repoPessoa */
        $repoPessoa = $this->doctrine->getRepository(Pessoa::class);
        /** @var Pessoa $pessoa */
        $pessoa = null;

        $sacadoChoices = null;
        if (!isset($choices['sacado'])) {
            $choices['sacado'] = $movimentacao->getSacado() ? [$movimentacao->getSacado() => $movimentacao->getSacado()] : null;
        }
        if (isset($choices['sacado'])) {
            $pessoa = $repoPessoa->find(current($choices['sacado']));
            $sacadoChoices = [$pessoa->getNomeMontadoComDocumento() => $pessoa->getId()];
        }
        $form->add('sacado', ChoiceType::class, [
            'label' => 'Sacado',
            'required' => false,
            'choices' => $sacadoChoices ?? null,
            'data' => $pessoa && $pessoa->getId() ? $pessoa->getId() : null,
            'attr' => isset($choices['sacado']) ? ['class' => 'autoSelect2'] : [
                'data-route-url' => '/base/pessoa/findByStr/',
                'data-text-format' => '%(nomeMontadoComDocumento)s',
                'data-val' => $movimentacao && $movimentacao->getSacado() ? $movimentacao->getSacado() : '',
                'class' => 'autoSelect2'
            ]
        ]);

        $cedenteChoices = null;
        if (!isset($choices['cedente'])) {
            $choices['cedente'] = $movimentacao->getCedente() ? [$movimentacao->getCedente() => $movimentacao->getCedente()] : null;
        }
        if (isset($choices['cedente'])) {
            /** @var Pessoa $pessoa */
            $pessoa = $repoPessoa->find(current($choices['cedente']));
            $cedenteChoices = [$pessoa->getNomeMontadoComDocumento() => $pessoa->getId()];
        }
        $form->add('cedente', ChoiceType::class, [
            'label' => 'Cedente',
            'required' => false,
            'choices' => $cedenteChoices ?? null,
            'attr' => [
                'data-route-url' => '/base/pessoa/findByStr/',
                'data-text-format' => '%(nomeMontadoComDocumento)s',
                'class' => 'autoSelect2'
            ]
        ]);


        // Adiciono este por default, sabendo que será alterado no beforeSave
        $form->add('status', HiddenType::class, [
            'label' => 'Status',
            'data' => 'ABERTA',
            'required' => false
        ]);

        /** @var CarteiraRepository $repoCarteira */
        $repoCarteira = $this->doctrine->getRepository(Carteira::class);

        $carteiraChoices =
            $choices['carteiras'] ??
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
            $choices['carteirasDestino'] ??
            $repoCarteira->findByFiltersSimpl([['atual', 'EQ', true]], ['e.codigo' => 'ASC'], 0, -1);
        $form->add('carteiraDestino', EntityType::class, [
            'label' => 'Destino',
            'class' => Carteira::class,
            'choice_label' => 'descricaoMontada',
            'choices' => $carteiraDestinoChoices,
            'attr' => [
                'data-val' => (null !== $movimentacao and null !== $movimentacao->getCarteiraDestino()) ? $movimentacao->getCarteiraDestino()->getId() : '',
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
            'data' => (null !== $movimentacao and null !== $movimentacao->getGrupoItem()) ? $movimentacao->getGrupoItem() : null,
            'required' => false,
            'disabled' => true
        ]);


        $modoChoices = $choices['modos'] ?? $this->doctrine->getRepository(Modo::class)->findAll(['codigo' => 'ASC']);

        $form->add('modo', EntityType::class, [
            'label' => 'Modo',
            'class' => Modo::class,
            'data' => $movimentacao->getModo(),
            'placeholder' => '...',
            'choices' => $modoChoices,
            'empty_data' => 0,
            'choice_label' => function (?Modo $modo) {
                return $modo ? $modo->getDescricaoMontada() : null;
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('bandeiraCartao', EntityType::class, [
            'label' => 'Bandeira',
            'class' => BandeiraCartao::class,
            'choices' => $this->doctrine->getRepository(BandeiraCartao::class)->findAll(WhereBuilder::buildOrderBy('descricao')),
            'choice_label' => function (?BandeiraCartao $bandeiraCartao) {
                return $bandeiraCartao ? $bandeiraCartao->getDescricao() : '';
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('operadoraCartao', EntityType::class, [
            'label' => 'Operadora',
            'class' => OperadoraCartao::class,
            'choices' => $this->doctrine->getRepository(OperadoraCartao::class)->findAll(WhereBuilder::buildOrderBy('descricao')),
            'choice_label' => function (?OperadoraCartao $operadoraCartao) {
                return $operadoraCartao ? $operadoraCartao->getDescricao() : '';
            },
            'required' => false,
            'attr' => ['class' => 'autoSelect2']
        ]);

        $form->add('centroCusto', EntityType::class, [
            'label' => 'Centro de Custo',
            'class' => CentroCusto::class,
            'data' => $movimentacao->getCentroCusto(),
            'empty_data' => $movimentacao->getCentroCusto(),
            'choices' => $this->doctrine->getRepository(CentroCusto::class)->findAll(),
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
                return sprintf('%03d', $banco->getCodigoBanco()) . ' - ' . $banco->getNome();
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