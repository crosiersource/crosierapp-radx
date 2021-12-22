<?php

namespace App\Form\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Form\Select2TagsType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Romaneio;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\FornecedorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class RomaneioType extends AbstractType
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

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('est_romaneio_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var Romaneio $romaneio */
            $romaneio = $event->getData();
            $builder = $event->getForm();

            $disabled = $romaneio->getId() && $romaneio->status !== 'INICIADO';

            $builder->add('id', TextType::class, [
                'label' => 'ID',
                'disabled' => true,
            ]);


            $notaFiscalChoices = [];
            $notaFiscalVal = null;
            if ($romaneio->notaFiscal) {
                $notaFiscalChoices = [$romaneio->notaFiscal];
                $notaFiscalVal = $romaneio->notaFiscal;
            }
            $builder->add('notaFiscal', EntityType::class, [
                'label' => 'Origem',
                'class' => NotaFiscal::class,
                'choices' => $notaFiscalChoices,
                'data' => $notaFiscalVal ?? null,
                'choice_label' => function (?NotaFiscal $notaFiscal) {
                    return $notaFiscal ? $notaFiscal->numero . ' - ' . $notaFiscal->xNomeEmitente : null;
                },
                'attr' => [
                    'class' => 'autoSelect2',
                    'data-val' => $notaFiscalVal ? $notaFiscalVal->getId() : '',
                ]
            ]);

            $builder->add('status', TextType::class, [
                'label' => 'Status',
                'disabled' => true
            ]);

            $builder->add('dtEmissao', DateTimeType::class, [
                'label' => 'Dt Emissão',
                'widget' => 'single_text',
                'required' => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => [
                    'class' => 'crsr-datetime focusOnReady'
                ],
                'disabled' => $disabled,
            ]);


            $builder->add('dtPrevEntrega', DateTimeType::class, [
                'label' => 'Dt Prev Entrega',
                'widget' => 'single_text',
                'required' => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'crsr-date'
                ],
                'disabled' => $disabled,
            ]);


            $builder->add('dtEntrega', DateTimeType::class, [
                'label' => 'Dt Prev Entrega',
                'widget' => 'single_text',
                'required' => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'crsr-date'
                ],
                'disabled' => $disabled,
            ]);

            $prazosPagtoChoices = [];
            if ($romaneio->prazosPagto) {
                $tags = explode(', ', $romaneio->prazosPagto);
                $prazosPagtoChoices = array_combine($tags, $tags);
            }
            $builder->add('prazosPagto', Select2TagsType::class, [
                'label' => 'Prazos',
                'required' => false,
                'choices' => $prazosPagtoChoices,
                'multiple' => true,
                'attr' => [
                    'class' => 'autoSelect2 s2allownew',
                    'data-tags' => 'true',
                    'data-token-separators' => ','
                ]
            ]);


            $fornecedorChoices = [];
            $fornecedorVal = null;
            if ($romaneio->fornecedor) {
                $fornecedorChoices = [$romaneio->fornecedor];
                $fornecedorVal = $romaneio->fornecedor;
            }
            $builder->add('fornecedor', EntityType::class, [
                'label' => 'Fornecedor',
                'class' => Fornecedor::class,
                'choices' => $fornecedorChoices,
                'data' => $fornecedorVal ?? null,
                'choice_label' => function (?Fornecedor $fornecedor) {
                    return $fornecedor ? $fornecedor->nome : null;
                },
                'attr' => [
                    'class' => 'autoSelect2',
                    'data-val' => $fornecedorVal ? $fornecedorVal->getId() : '',
                    'data-route-url' => '/est/fornecedor/findByStr',
                ]
            ]);

            $builder->add('valorTotal', MoneyType::class, [
                'label' => 'Valor Total',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true,
                'required' => false
            ]);

            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $romaneio->jsonData]);

        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                if (isset($event->getData()['fornecedor']) && $event->getData()['fornecedor']) {
                    $fornecedorId = $event->getData()['fornecedor'];
                    /** @var FornecedorRepository $repoFornecedor */
                    $repoFornecedor = $this->doctrine->getRepository(Fornecedor::class);
                    $fornecedor = $repoFornecedor->find($fornecedorId);
                    $fornecedorChoices = [$fornecedor];
                    $form->remove('fornecedor');
                    $form->add('fornecedor', EntityType::class, [
                        'class' => Fornecedor::class,
                        'choices' => $fornecedorChoices,
                        'data' => $fornecedor ?? null,
                        'choice_label' => 'nome',
                    ]);
                }

                if (isset($event->getData()['prazosPagto']) && is_array($event->getData()['prazosPagto'])) {
                    $prazosPagto = $event->getData()['prazosPagto'];

                    $prazosPagtoChoices = array_combine($prazosPagto, $prazosPagto);
                    $form->remove('prazosPagto');
                    $form->add('prazosPagto', Select2TagsType::class, [
                        'choices' => $prazosPagtoChoices,
                    ]);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Romaneio::class
        ));
    }
}