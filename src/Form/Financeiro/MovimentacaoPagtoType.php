<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\PessoaRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MovimentacaoAPagarReceberType.
 *
 * Form para lançamento de contas a pagar.
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoPagtoType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    private MovimentacaoTypeBuilder $movimentacaoTypeBuilder;


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

            $options = [];

            $repoCarteira = $this->doctrine->getRepository(Carteira::class);
            $carteiras = $repoCarteira->findBy(['concreta' => true, 'atual' => true], ['codigo' => 'ASC']);

            $options['carteiras'] = $carteiras;

            $options['sacado'] = 'default'; // só para passar pelo 'isset' (e setar o restante do campo no padrão)
            $options['cedente'] = 'default'; // só para passar pelo 'isset' (e setar o restante do campo no padrão)

            $this->movimentacaoTypeBuilder->build($form, $movimentacao, $options);
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                $choices = [];

                if ($event->getData()['cedente'] ?? false) {
                    $cedente = $event->getData()['cedente'];
                    $choices['cedente']['choices'] = [$cedente => $cedente];
                }

                if ($event->getData()['sacado'] ?? false) {
                    $sacado = $event->getData()['sacado'];
                    $choices['sacado']['choices'] = [$sacado => $sacado];
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
        $resolver->setDefaults([
            'data_class' => Movimentacao::class
        ]);
    }
}
