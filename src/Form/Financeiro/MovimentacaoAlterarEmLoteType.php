<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Utils\RepositoryUtils\WhereBuilder;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\TipoLancto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MovimentacaoAlterarEmLoteType.
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoAlterarEmLoteType extends AbstractType
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

            $this->movimentacaoTypeBuilder->build($form, $movimentacao);

            $form->remove('id');
            $form->remove('uuid');

            $form->remove('tipoLancto');
            $form->add('tipoLancto', EntityType::class, [
                'label' => 'Tipo Lancto',
                'class' => TipoLancto::class,
                'empty_data' => null,
                'choices' => $this->doctrine->getRepository(TipoLancto::class)->findAll(WhereBuilder::buildOrderBy('codigo')),
                'choice_label' => 'descricaoMontada',
                'required' => false,
                'attr' => [
                    'class' => 'autoSelect2 focusOnReady'
                ]
            ]);


            $form->remove('status');
            $form->add('status', ChoiceType::class, [
                'label' => 'Status',
                'placeholder' => '',
                'choices' => [
                    'ABERTA' => 'ABERTA',
                    'REALIZADA' => 'REALIZADA'
                ],
                'required' => false,
                'attr' => ['class' => 'autoSelect2']
            ]);

            $form->add('quitado', ChoiceType::class, [
                'label' => 'Quitado',
                'placeholder' => '',
                'choices' => [
                    'Sim' => true,
                    'Não' => false
                ],
                'required' => false
            ]);


        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                $cedente = $event->getData()['cedente'] ?: null;
                $form->remove('cedente');
                $form->add('cedente', ChoiceType::class, [
                    'label' => 'Cedente',
                    'required' => false,
                    'choices' => [$cedente]
                ]);

                $sacado = $event->getData()['sacado'] ?: null;
                $form->remove('sacado');
                $form->add('sacado', ChoiceType::class, [
                    'label' => 'Sacado',
                    'required' => false,
                    'choices' => [$sacado]
                ]);
            }
        );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Movimentacao::class
        ]);
    }
}