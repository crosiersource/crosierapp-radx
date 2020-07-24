<?php

namespace App\Form\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Unidade;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\DeptoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\FornecedorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\GrupoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\UnidadeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoType extends AbstractType
{

    /** @var EntityManagerInterface */
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('est_produto_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var Produto $produto */
            $produto = $event->getData();
            $builder = $event->getForm();

            $builder->add('id', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['readonly' => 'readonly']
            ]);

            $builder->add('nome', TextType::class, [
                'label' => 'Nome',
                'attr' => ['class' => 'focusOnReady'],
            ]);

            /** @var DeptoRepository $repoDeptos */
            $repoDeptos = $this->doctrine->getRepository(Depto::class);
            $deptos = $repoDeptos->findAll(['codigo' => 'ASC']);
            $builder->add('depto', EntityType::class, array(
                'label' => 'Depto',
                'placeholder' => '...',
                'class' => Depto::class,
                'choices' => $deptos,
                'choice_label' => function (?Depto $depto) {
                    return $depto ? $depto->getDescricaoMontada() : null;
                },
            ));

            $grupos = [];
            if ($produto && $produto->depto ?? false) {
                /** @var GrupoRepository $repoGrupos */
                $repoGrupos = $this->doctrine->getRepository(Grupo::class);
                $grupos = $repoGrupos->findBy(['depto' => $produto->depto], ['codigo' => 'ASC']);
            }
            $builder->add('grupo', EntityType::class, [
                'label' => 'Grupo',
                'placeholder' => '...',
                'class' => Grupo::class,
                'choices' => $grupos,
                'choice_label' => 'descricaoMontada',
            ]);

            $subgrupos = [];
            if ($produto && $produto->grupo ?? false) {
                /** @var GrupoRepository $repoGrupos */
                $repoSubgrupos = $this->doctrine->getRepository(Subgrupo::class);
                $subgrupos = $repoSubgrupos->findBy(['grupo' => $produto->grupo], ['codigo' => 'ASC']);
            }
            $builder->add('subgrupo', EntityType::class, [
                'label' => 'Subgrupo',
                'placeholder' => '...',
                'class' => Subgrupo::class,
                'choices' => $subgrupos,
                'choice_label' => 'descricaoMontada',
                'required' => true
            ]);

            /** @var FornecedorRepository $repoFornecedor */
            $repoFornecedor = $this->doctrine->getRepository(Fornecedor::class);
            $fornecedores = $repoFornecedor->findAll(['nome' => 'ASC']);
            $builder->add('fornecedor', EntityType::class, [
                'label' => 'Fornecedor',
                'placeholder' => '...',
                'class' => Fornecedor::class,
                'choices' => $fornecedores,
                'choice_label' => 'nome',
                'attr' => [
                    'class' => 'autoSelect2'
                ],
                'required' => true
            ]);

            /** @var UnidadeRepository $repoUnidade */
            $repoUnidade = $this->doctrine->getRepository(Unidade::class);
            $unidades = $repoUnidade->findAll(['label' => 'ASC']);
            $builder->add('unidadePadrao', EntityType::class, [
                'label' => 'Unidade',
                'placeholder' => '...',
                'class' => Unidade::class,
                'choices' => $unidades,
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'autoSelect2'
                ],
                'required' => true
            ]);

            $builder->add('UUID', HiddenType::class, [
                'label' => 'UUID',
                'attr' => ['readonly' => true, 'class' => 'notuppercase']
            ]);

            $builder->add('updated', DateTimeType::class, array(
                'label' => 'Data/Hora',
                'widget' => 'single_text',
                'required' => false,
                'disabled' => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => [
                    'class' => 'crsr-datetime',
                ]
            ));

            $builder->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'ATIVO' => 'ATIVO',
                    'INATIVO' => 'INATIVO'
                ],
                'attr' => [
                    'class' => 'autoSelect2'
                ],
            ]);

            $builder->add('composicao', ChoiceType::class, [
                'label' => 'Composição',
                'choices' => [
                    'Sim' => 'S',
                    'Não' => 'N'
                ],
                'attr' => [
                    'class' => 'autoSelect2'
                ]

            ]);

            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => ($produto->jsonData ?? null)]);

        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {

                $builder = $event->getForm();
                $data = $event->getData();

                $builder->remove('jsonData');
                $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $data['jsonData'] ?? null]);

                $builder->add('depto', EntityType::class, [
                    'class' => Depto::class,
                    'choice_label' => 'descricaoMontada'
                ]);

                $builder->add('grupo', EntityType::class, [
                    'class' => Grupo::class,
                    'choice_label' => 'descricaoMontada'
                ]);

                $builder->add('subgrupo', EntityType::class, [
                    'class' => Subgrupo::class,
                    'choice_label' => 'descricaoMontada'
                ]);

            }
        );

        // Necessário para os casos onde o formulário não tem todos os campos do json_data (para que eles não desapareçam por conta disto)
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {
                /** @var Produto $produto */
                $produto = $event->getData();
                if ($produto->getId()) {
                    $jsonDataOrig = json_decode($this->doctrine->getConnection()->fetchAssoc('SELECT json_data FROM est_produto WHERE id = :id', ['id' => $produto->getId()])['json_data'] ?? '{}', true);
                    $produto->jsonData = array_merge($jsonDataOrig, $produto->jsonData);
                    $event->setData($produto);
                }
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produto::class
        ]);
    }
}