<?php

namespace App\Form\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalType extends AbstractType
{

    /** @var EntityManagerInterface */
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    private NotaFiscalBusiness $notaFiscalBusiness;

    /**
     * @required
     * @param NotaFiscalBusiness $notaFiscalBusiness
     */
    public function setNotaFiscalBusiness(NotaFiscalBusiness $notaFiscalBusiness): void
    {
        $this->notaFiscalBusiness = $notaFiscalBusiness;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('fis_nf_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $event->getData();
            $builder = $event->getForm();

            $emitentes = $this->notaFiscalBusiness->getEmitentes();

            $disabled = false;
            if ($notaFiscal) {
                if (!$this->notaFiscalBusiness->permiteSalvar($notaFiscal)) {
                    $disabled = true;
                }
                if ($notaFiscal->getDocumentoEmitente() && !in_array($notaFiscal->getDocumentoEmitente(), $emitentes)) {
                    $disabled = true;
                }
            }
            $disabledCancelamento = !($notaFiscal && $this->notaFiscalBusiness->permiteCancelamento($notaFiscal));
            $disabledTransp = $disabled || ($notaFiscal && $notaFiscal->getTranspModalidadeFrete() === 'SEM_FRETE');

            $builder->add('id', HiddenType::class, [
                'required' => false,
                // atributo utilizado para que o javascript possa localizar facilmente este input
                'attr' => [
                    'class' => 'ID_ENTITY'
                ]
            ]);

            $builder->add('infoStatus', TextType::class, [
                'label' => 'Info Status',
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('tipoNotaFiscal', ChoiceType::class, [
                'label' => 'Tipo',
                'expanded' => true,
                'label_attr' => [
                    'class' => 'radio-inline'
                ],
                'choices' => [
                    'Nota Fiscal' => 'NFE',
                    'Cupom Fiscal' => 'NFCE'
                ],
                'attr' => [
                    'class' => 'TIPO_FISCAL'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('numero', IntegerType::class, [
                'label' => 'Número',
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('serie', IntegerType::class, [
                'label' => 'Série',
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('uuid', TextType::class, [
                'label' => 'UUID',
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('chaveAcesso', TextType::class, [
                'label' => 'Chave',
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('protocoloAutorizacao', TextType::class, [
                'label' => 'Prot Autoriz',
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('dtEmissao', DateTimeType::class, [
                'label' => 'Dt Emissão',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => ['class' => 'crsr-datetime'],
                'disabled' => $disabled
            ]);

            $builder->add('dtSaiEnt', DateTimeType::class, [
                'label' => 'Dt Saída/Entrada',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => ['class' => 'crsr-datetime'],
                'disabled' => $disabled
            ]);


            $choicesEmitentes = [];
            foreach ($emitentes as $emitente) {
                $chave = StringUtils::mascararCnpjCpf($emitente['cnpj']) . ' - ' . $emitente['razaosocial'];
                $choicesEmitentes[$chave] = $emitente['cnpj'];
            }
            $builder->add('documentoEmitente', ChoiceType::class, [
                'label' => 'Emitente',
                'choices' => $choicesEmitentes,
                'required' => true,
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);

            $builder->add('documentoDestinatario', TextType::class, [
                'label' => 'CPF/CNPJ',
                'required' => false,
                'attr' => [
                    'class' => 'cpfCnpj'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('xNomeDestinatario', TextType::class, [
                'label' => 'Nome/Razão Social',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('inscricaoEstadualDestinatario', TextType::class, [
                'label' => 'Inscr Estadual',
                'required' => false,
                'disabled' => $disabled
            ]);
            $builder->add('cepDestinatario', TextType::class, [
                'label' => 'CEP',
                'required' => false,
                'disabled' => $disabled,
                'attr' => [
                    'class' => 'cepComBtnConsulta',
                    'data-campo-logradouro' => 'nota_fiscal_logradouroDestinatario',
                    'data-campo-bairro' => 'nota_fiscal_bairroDestinatario',
                    'data-campo-cidade' => 'nota_fiscal_cidadeDestinatario',
                    'data-campo-estado' => 'nota_fiscal_estadoDestinatario',
                ],
            ]);
            $builder->add('logradouroDestinatario', TextType::class, [
                'label' => 'Logradouro',
                'required' => false,
                'disabled' => $disabled
            ]);
            $builder->add('numeroDestinatario', TextType::class, [
                'label' => 'Número',
                'required' => false,
                'disabled' => $disabled
            ]);
            $builder->add('bairroDestinatario', TextType::class, [
                'label' => 'Bairro',
                'required' => false,
                'disabled' => $disabled
            ]);
            $builder->add('cidadeDestinatario', TextType::class, [
                'label' => 'Cidade',
                'required' => false,
                'disabled' => $disabled
            ]);
            $builder->add('estadoDestinatario', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => [
                    'Acre' => 'AC',
                    'Alagoas' => 'AL',
                    'Amapá' => 'AP',
                    'Amazonas' => 'AM',
                    'Bahia' => 'BA',
                    'Ceará' => 'CE',
                    'Distrito Federal' => 'DF',
                    'Espírito Santo' => 'ES',
                    'Goiás' => 'GO',
                    'Maranhão' => 'MA',
                    'Mato Grosso' => 'MT',
                    'Mato Grosso do Sul' => 'MS',
                    'Minas Gerais' => 'MG',
                    'Pará' => 'PA',
                    'Paraíba' => 'PB',
                    'Paraná' => 'PR',
                    'Pernambuco' => 'PE',
                    'Piauí' => 'PI',
                    'Rio de Janeiro' => 'RJ',
                    'Rio Grande do Norte' => 'RN',
                    'Rio Grande do Sul' => 'RS',
                    'Rondônia' => 'RO',
                    'Roraima' => 'RR',
                    'Santa Catarina' => 'SC',
                    'São Paulo' => 'SP',
                    'Sergipe' => 'SE',
                    'Tocantins' => 'TO'
                ],
                'required' => false,
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);
            $builder->add('foneDestinatario', TextType::class, [
                'label' => 'Fone',
                'required' => false,
                'attr' => [
                    'class' => 'telefone'
                ],
                'disabled' => $disabled
            ]);
            $builder->add('emailDestinatario', TextType::class, [
                'label' => 'E-mail',
                'attr' => [
                    'class' => 'email'
                ],
                'required' => false,
                'disabled' => $disabled
            ]);


            $builder->add('motivoCancelamento', TextType::class, [
                'label' => 'Motivo do Cancelamento',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 15,
                        'max' => 255
                    ])
                ],
                'disabled' => $disabledCancelamento
            ]);


            $builder->add('naturezaOperacao', TextType::class, [
                'label' => 'Natureza da Operação',
                'required' => true,
                'disabled' => $disabled,
                'attr' => ['maxlength' => 60]
            ]);

            $builder->add('entradaSaida', ChoiceType::class, [
                'label' => 'Entrada/Saída',
                'required' => true,
                'choices' => [
                    'Entrada' => 'E',
                    'Saída' => 'S'
                ],
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);

            // Campos para FRETE

            $builder->add('transpModalidadeFrete', ChoiceType::class, [
                'label' => 'Modalidade Frete',
                'required' => true,
                'choices' => [
                    'Sem frete' => 'SEM_FRETE',
                    'Por conta do emitente' => 'EMITENTE',
                    'Por conta do destinatário/remetente' => 'DESTINATARIO',
                    'Por conta de terceiros' => 'TERCEIROS'
                ],
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);


            $builder->add('transpDocumento', TextType::class, [
                'label' => 'CPF/CNPJ',
                'required' => false,
                'disabled' => $disabledTransp,
                'attr' => [
                    'class' => 'cpfCnpj'
                ],
            ]);

            $builder->add('transpNome', TextType::class, [
                'label' => 'Nome / Razão Social',
                'required' => false,
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpEndereco', TextType::class, [
                'label' => 'Endereço',
                'required' => false,
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpCidade', TextType::class, [
                'label' => 'Cidade',
                'required' => false,
                'disabled' => $disabledTransp
            ]);
            $builder->add('transpEstado', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => [
                    'Acre' => 'AC',
                    'Alagoas' => 'AL',
                    'Amapá' => 'AP',
                    'Amazonas' => 'AM',
                    'Bahia' => 'BA',
                    'Ceará' => 'CE',
                    'Distrito Federal' => 'DF',
                    'Espírito Santo' => 'ES',
                    'Goiás' => 'GO',
                    'Maranhão' => 'MA',
                    'Mato Grosso' => 'MT',
                    'Mato Grosso do Sul' => 'MS',
                    'Minas Gerais' => 'MG',
                    'Pará' => 'PA',
                    'Paraíba' => 'PB',
                    'Paraná' => 'PR',
                    'Pernambuco' => 'PE',
                    'Piauí' => 'PI',
                    'Rio de Janeiro' => 'RJ',
                    'Rio Grande do Norte' => 'RN',
                    'Rio Grande do Sul' => 'RS',
                    'Rondônia' => 'RO',
                    'Roraima' => 'RR',
                    'Santa Catarina' => 'SC',
                    'São Paulo' => 'SP',
                    'Sergipe' => 'SE',
                    'Tocantins' => 'TO'
                ],
                'required' => false,
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpEspecieVolumes', TextType::class, [
                'label' => 'Espécie Volumes',
                'required' => false,
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpMarcaVolumes', TextType::class, [
                'label' => 'Marca Volumes',
                'required' => false,
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpNumeracaoVolumes', TextType::class, [
                'label' => 'Marca Volumes',
                'required' => false,
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpPesoBruto', NumberType::class, [
                'label' => 'Peso Bruto',
                'grouping' => 'true',
                'scale' => 3,
                'attr' => [
                    'class' => 'crsr-dec3'
                ],
                'required' => false,
                'help' => 'Em kg',
                'empty_data' => '',
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpPesoLiquido', NumberType::class, [
                'label' => 'Peso Líquido',
                'grouping' => 'true',
                'scale' => 3,
                'attr' => [
                    'class' => 'crsr-dec3'
                ],
                'required' => false,
                'help' => 'Em kg',
                'disabled' => $disabledTransp
            ]);

            $builder->add('transpQtdeVolumes', IntegerType::class, [
                'label' => 'Qtde Volumes',
                'required' => false,
                'disabled' => $disabledTransp
            ]);

            $builder->add('indicadorFormaPagto', ChoiceType::class, [
                'label' => 'Forma Pagto',
                'required' => true,
                'choices' => [
                    'A vista' => 'VISTA',
                    'A prazo' => 'PRAZO',
                    'Outros' => 'OUTROS'
                ],
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);

            $builder->add('finalidadeNf', ChoiceType::class, [
                'label' => 'Finalidade',
                'required' => true,
                'choices' => [
                    'Normal' => 'NORMAL',
                    'Devolução' => 'DEVOLUCAO',
                    'Ajuste' => 'AJUSTE',
                    'Complementar' => 'COMPLEMENTAR'
                ],
                'attr' => ['class' => 'autoSelect2'],
                'disabled' => $disabled
            ]);

            $builder->add('infoCompl', TextareaType::class, [
                'label' => 'Info Compl',
                'required' => false,
                'attr' => [
                    'rows' => '5'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('a03idNfReferenciada', TextType::class, [
                'label' => 'Id NF Referenciada',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('subtotal', MoneyType::class, [
                'label' => 'Subtotal',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true
            ]);

            $builder->add('totalDescontos', MoneyType::class, [
                'label' => 'Descontos',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true
            ]);

            $builder->add('valorTotal', MoneyType::class, [
                'label' => 'Valor Total',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true
            ]);

            $builder->add('manifestDest', TextType::class, [
                'label' => 'Manifestação',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('dtManifestDest', DateTimeType::class, [
                'label' => 'Dt Manifest',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => ['class' => 'crsr-datetime'],
                'disabled' => true
            ]);

            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => ($notaFiscal->jsonData ?? null)]);

        });

        // Necessário para os casos onde o formulário não tem todos os campos do json_data (para que eles não desapareçam por conta disto)
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {
                /** @var NotaFiscal $notaFiscal */
                $notaFiscal = $event->getData();
                if ($notaFiscal->getId()) {
                    $jsonDataOrig = json_decode($this->doctrine->getConnection()->fetchAssoc('SELECT json_data FROM fis_nf WHERE id = :id', ['id' => $notaFiscal->getId()])['json_data'] ?? '{}', true);
                    $notaFiscal->jsonData = array_merge($jsonDataOrig, $notaFiscal->jsonData);
                    $event->setData($notaFiscal);
                }
            }
        );

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NotaFiscal::class
        ));
    }

}