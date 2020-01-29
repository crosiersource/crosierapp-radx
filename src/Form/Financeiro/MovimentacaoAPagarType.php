<?php

namespace App\Form\Financeiro;

use App\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\PessoaRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\ChoiceTypeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MovimentacaoAPagarType.
 *
 * Form para lançamento de contas a pagar.
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoAPagarType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $doctrine;

    /** @var MovimentacaoTypeBuilder */
    private $movimentacaoTypeBuilder;


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
     * @param MovimentacaoTypeBuilder $movimentacaoTypeBuilder
     */
    public function setMovimentacaoTypeBuilder(MovimentacaoTypeBuilder $movimentacaoTypeBuilder): void
    {
        $this->movimentacaoTypeBuilder = $movimentacaoTypeBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Movimentacao $movimentacao */
            $movimentacao = $event->getData();
            $form = $event->getForm();

            $choices = [];

            /** @var PessoaRepository $repoPessoa */
            $repoPessoa = $this->doctrine->getRepository(Pessoa::class);

            $filiaisR = $repoPessoa->findByFiltersSimpl([['categ.descricao', 'LIKE', 'FILIAL PROP']]);
            if ($filiaisR) {
                $filiais = ChoiceTypeUtils::toChoiceTypeChoices($filiaisR, '%08d %s', ['id', 'nome']);
                $choices['sacado'] = $filiais;
            }

            $this->movimentacaoTypeBuilder->build($form, $movimentacao, $choices);

        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                $choices = [];

                if ($event->getData()['cedente']) {
                    $cedente = (int)$event->getData()['cedente'];
                    $choices['cedente'] = [$cedente => $cedente];
                }

                if ($event->getData()['sacado']) {
                    $sacado = (int)$event->getData()['sacado'];
                    $choices['sacado'] = [$sacado => $sacado];
                }

                $this->movimentacaoTypeBuilder->build($form, null, $choices);
            }
        );


    }

    public function getBlockPrefix()
    {
        return 'movimentacao';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Movimentacao::class
        ));
    }
}