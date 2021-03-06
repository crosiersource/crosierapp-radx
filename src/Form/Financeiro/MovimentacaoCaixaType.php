<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Banco;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\BandeiraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Modo;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\OperadoraCartao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoCaixaType extends AbstractType
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


            // $builder = $event->getForm();
            if (!$movimentacao) {
                $movimentacao = new Movimentacao();
            }

            $disabled = false;
            // Não permitir edição de 60 - TRANSFERÊNCIA ENTRE CARTEIRAS ou 61 - TRANSFERÊNCIA DE ENTRADA DE CAIXA
            if ($movimentacao->getId() && $movimentacao->tipoLancto &&
                in_array($movimentacao->tipoLancto->getCodigo(), [60, 61], true)) {
                $disabled = true;
            }

            $form->add('id', IntegerType::class, [
                'label' => 'Id',
                'disabled' => true,
                'required' => false
            ]);


            $repoTipoLancto = $this->doctrine->getRepository(TipoLancto::class);
            $tiposLanctos =
                [
                    $repoTipoLancto->find(20),
                    $repoTipoLancto->find(60),
                    $repoTipoLancto->find(61)
                ];

            $form->add('tipoLancto', EntityType::class, [
                'label' => 'Tipo Lancto',
                'class' => TipoLancto::class,
                'empty_data' => $movimentacao->tipoLancto,
                'choices' => $tiposLanctos,
                'choice_label' => 'descricaoMontada',
                'required' => true,
                'attr' => [
                    'data-val' => $movimentacao->tipoLancto ? $movimentacao->tipoLancto->getId() : null
                ],
                'disabled' => $movimentacao->getId() ? true : false
            ]);

            $form->add('categoria', EntityType::class, [
                'label' => 'Categoria',
                'class' => Categoria::class,
                'choice_label' => 'descricaoMontadaTree',
                'choices' => null,
                'data' => $movimentacao->categoria,
                'empty_data' => $movimentacao->categoria,
                'attr' => [
                    'data-val' => (null !== $movimentacao and null !== $movimentacao->categoria and null !== $movimentacao->categoria->getId()) ? $movimentacao->categoria->getId() : '',
                    'class' => ''
                ],
                'required' => false,
                'disabled' => $disabled
            ]);

            $form->add('descricao', TextType::class, [
                'label' => 'Descrição',
                'required' => false,
                'disabled' => $disabled
            ]);


            // Para que o campo select seja montado já com o valor selecionado (no $('#movimentacao_carteira').val())
            $attr = [];
            if (null !== $movimentacao and null !== $movimentacao->carteira and null !== $movimentacao->carteira->getId()) {
                $attr['data-val'] = $movimentacao->carteira->getId();
                $attr['data-caixa'] = $movimentacao->carteira->caixa ? 'true' : 'false';
            }
            $form->add('carteira', EntityType::class, [
                'label' => 'Carteira',
                'class' => Carteira::class,
                'choice_label' => function (?Carteira $carteira) {
                    return $carteira ? $carteira->getDescricaoMontada() : null;
                },
                'choices' => $this->doctrine->getRepository(Carteira::class)->findAll(),
                'attr' => $attr,
                'required' => false,
                'disabled' => $disabled
            ]);


            // só é obrigatório nos casos de tipoLancto 60 | TRANSFERÊNCIA ENTRE CARTEIRAS
            $form->add('carteiraDestino', EntityType::class, [
                'label' => 'Destino',
                'class' => Carteira::class,
                'choice_label' => 'descricaoMontada',
                'choices' => null,
                'attr' => [
                    'data-val' => (null !== $movimentacao and null !== $movimentacao->carteiraDestino) ? $movimentacao->carteiraDestino->getId() : '',
                ],
                'required' => false,
                'disabled' => $disabled
            ]);


            $repoModo = $this->doctrine->getRepository(Modo::class);
            $modos =
                [
                    $repoModo->findOneBy(['codigo' => 1]),
                    $repoModo->findOneBy(['codigo' => 4]),
                    $repoModo->findOneBy(['codigo' => 9]),
                    $repoModo->findOneBy(['codigo' => 10]),
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

            $form->add('bandeiraCartao', EntityType::class, [
                'label' => 'Bandeira',
                'class' => BandeiraCartao::class,
                'choices' => $this->doctrine->getRepository(BandeiraCartao::class)->findAll(WhereBuilder::buildOrderBy('descricao')),
                'choice_label' => function (?BandeiraCartao $bandeiraCartao) {
                    return $bandeiraCartao ? $bandeiraCartao->descricao : '';
                },
                'required' => false,
                'disabled' => $disabled
            ]);

            $form->add('operadoraCartao', EntityType::class, [
                'label' => 'Operadora',
                'class' => OperadoraCartao::class,
                'choices' => $this->doctrine->getRepository(OperadoraCartao::class)->findAll(WhereBuilder::buildOrderBy('descricao')),
                'choice_label' => function (?OperadoraCartao $operadoraCartao) {
                    return $operadoraCartao ? $operadoraCartao->descricao : '';
                },
                'required' => false,
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);


            $form->add('dtMoviment', DateType::class, [
                'label' => 'Dt Moviment',
                'help' => 'Data em que a compra/venda foi efetuada ou que a cobrança foi recebida',
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

            // Cheque

            $form->add('chequeBanco', EntityType::class, [
                'label' => 'Banco',
                'class' => Banco::class,
                'choices' => $this->doctrine->getRepository(Banco::class)
                    ->findAll(WhereBuilder::buildOrderBy('codigoBanco')),
                'choice_label' => function (Banco $banco) {
                    return sprintf("%03d", $banco->getCodigoBanco()) . " - " . $banco->nome;
                },
                'required' => false,
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);

            $form->add('chequeAgencia', TextType::class, [
                'label' => 'Agência',
                'required' => false,
                'disabled' => $disabled
            ]);

            $form->add('chequeConta', TextType::class, [
                'label' => 'Conta',
                'required' => false,
                'disabled' => $disabled
            ]);

            $form->add('chequeNumCheque', TextType::class, [
                'label' => 'Núm Cheque',
                'required' => false,
                'disabled' => $disabled
            ]);


        });
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
}