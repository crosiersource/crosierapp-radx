<?php

namespace App\Form\Estoque;

use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Fornecedor;
use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\Subgrupo;
use App\Entity\Estoque\UnidadeProduto;
use App\Repository\Estoque\DeptoRepository;
use App\Repository\Estoque\FornecedorRepository;
use App\Repository\Estoque\GrupoRepository;
use App\Repository\Estoque\UnidadeProdutoRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Produto $produto */
            $produto = $event->getData();
            $builder = $event->getForm();

            $builder->add('id', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'disabled' => true,
            ]);

            $builder->add('nome', TextType::class, [
                'label' => 'Nome',
                'attr' => ['class' => 'focusOnReady'],
            ]);

            $builder->add('titulo', TextType::class, [
                'label' => 'Título',
                'required' => false,
                'attr' => ['class' => 'notuppercase'],
            ]);

            $builder->add('caracteristicas', TextareaType::class, [
                'label' => 'Características',
                'required' => false,
                'attr' => ['class' => 'summernote']
            ]);

            /** @var DeptoRepository $repoDeptos */
            $repoDeptos = $this->doctrine->getRepository(Depto::class);
            $deptos = $repoDeptos->findAll(['codigo' => 'ASC']);
            $builder->add('depto', EntityType::class, [
                'label' => 'Depto',
                'placeholder' => '...',
                'class' => Depto::class,
                'choices' => $deptos,
                'choice_label' => function (?Depto $depto) {
                    return $depto ? $depto->getDescricaoMontada() : null;
                },
                'attr' => [
                    'class' => 'autoSelect2'
                ],
            ]);

            $grupos = [];
            if ($produto && $produto->getDepto()) {
                /** @var GrupoRepository $repoGrupos */
                $repoGrupos = $this->doctrine->getRepository(Grupo::class);
                $grupos = $repoGrupos->findBy(['depto' => $produto->getDepto()], ['codigo' => 'ASC']);
            }
            $builder->add('grupo', EntityType::class, [
                'label' => 'Grupo',
                'placeholder' => '...',
                'class' => Grupo::class,
                'choices' => $grupos,
                'choice_label' => 'descricaoMontada',
                'attr' => [
                    'class' => 'autoSelect2'
                ],
            ]);

            $subgrupos = [];
            if ($produto && $produto->getGrupo()) {
                /** @var GrupoRepository $repoGrupos */
                $repoSubgrupos = $this->doctrine->getRepository(Subgrupo::class);
                $subgrupos = $repoSubgrupos->findBy(['grupo' => $produto->getGrupo()], ['codigo' => 'ASC']);
            }
            $builder->add('subgrupo', EntityType::class, [
                'label' => 'Subgrupo',
                'placeholder' => '...',
                'class' => Subgrupo::class,
                'choices' => $subgrupos,
                'choice_label' => 'descricaoMontada',
                'attr' => [
                    'class' => 'autoSelect2'
                ],
                'required' => true
            ]);

            $builder->add('ean', TextType::class, [
                'label' => 'EAN',
                'required' => false
            ]);

            $builder->add('referencia', TextType::class, [
                'label' => 'Referência',
                'required' => false
            ]);

            /** @var UnidadeProdutoRepository $repoUnidadeProduto */
            $repoUnidadeProduto = $this->doctrine->getRepository(UnidadeProduto::class);
            $unidades = $repoUnidadeProduto->findAll(['label' => 'ASC']);
            $builder->add('unidade', EntityType::class, [
                'label' => 'Unidade',
                'placeholder' => '...',
                'class' => UnidadeProduto::class,
                'choices' => $unidades,
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'autoSelect2'
                ],
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

            $builder->add('ncm', TextType::class, [
                'label' => 'NCM',
                'required' => false,
                'attr' => [
                    'form' => 'produto',
                ]
            ]);

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

            $builder->add('porcentPreench', PercentType::class, [
                'label' => 'Status Cad',
                'scale' => 0,
                'attr' => ['class' => 'int'],
                'required' => false,
                'disabled' => true
            ]);


        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {

                $builder = $event->getForm();

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

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produto::class
        ]);
    }
}