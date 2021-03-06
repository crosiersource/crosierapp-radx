<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\ChoiceTypeUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Carteira;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
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
class MovimentacaoChequeProprioType extends AbstractType
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

            $choices = [];

            $repoCarteira = $this->doctrine->getRepository(Carteira::class);
            $carteirasDeCheques = $repoCarteira->findBy(['cheque' => true], ['codigo' => 'ASC']);

            $choices['carteiras'] = $carteirasDeCheques;

            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->doctrine->getRepository(Cliente::class);
            $filiaisR = $repoCliente->findFiliaisProp();
            if ($filiaisR) {
                $filiais = ChoiceTypeUtils::toChoiceTypeChoices($filiaisR, '%08d %s', ['id', 'nome']);
                $choices['sacado'] = $filiais;
            } else {
                // não deveria, deveria ter ao menos 1 cadastrado no crm_cliente com json_data.filial_prop = "S"
                $choices['sacado'] = 'default'; // só para passar pelo 'isset' (e setar o restante do campo no padrão)
            }
            $choices['cedente'] = 'default'; // só para passar pelo 'isset' (e setar o restante do campo no padrão)
            $this->movimentacaoTypeBuilder->build($form, $movimentacao, $choices);
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                $choices = [];

                if ($event->getData()['cedente'] ?? null) {
                    $cedente = (int)$event->getData()['cedente'];
                    $choices['cedente'] = [$cedente => $cedente];
                }

                if ($event->getData()['sacado'] ?? null) {
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
        $resolver->setDefaults([
            'data_class' => Movimentacao::class
        ]);
    }
}
