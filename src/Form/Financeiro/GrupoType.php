<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Categoria;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Grupo;
use CrosierSource\CrosierLibRadxBundle\Repository\Financeiro\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GrupoType
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class GrupoType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('descricao', TextType::class, array(
            'label' => 'Descrição'
        ));

        $builder->add('diaVencto', IntegerType::class, array(
            'label' => 'Dia de Vencto',
            'required' => false
        ));

        $builder->add('diaInicioAprox', IntegerType::class, array(
            'label' => 'Dia de Início (aprox)',
            'required' => false
        ));

        $builder->add('ativo', ChoiceType::class, array(
            'choices' => array(
                'Sim' => true,
                'Não' => false
            )
        ));

        $repoCarteira = $this->doctrine->getRepository(Carteira::class);
        $carteiras = $repoCarteira->findAll(['codigo' => 'ASC']);
        $builder->add('carteiraPagantePadrao', EntityType::class, array(
            'label' => 'Carteira Pagante Padrão',
            'placeholder' => '...',
            'class' => Carteira::class,
            'choices' => $carteiras,
            'choice_label' => function (Carteira $carteira) {
                return $carteira->getDescricaoMontada();
            },
            'attr' => [
                'class' => 'autoSelect2'
            ],
            'required' => false
        ));

        /** @var CategoriaRepository $repoCategoria */
        $repoCategoria = $this->doctrine->getRepository(Categoria::class);
        $categorias = $repoCategoria->findAll(['codigoOrd' => 'ASC']);

        $builder->add('categoriaPadrao', EntityType::class, [
            'label' => 'Categoria',
            'class' => Categoria::class,
            'choice_label' => 'descricaoMontadaTree',
            'choices' => $categorias,
            'attr' => [
                'class' => 'autoSelect2'
            ],
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Grupo::class
        ]);
    }
}