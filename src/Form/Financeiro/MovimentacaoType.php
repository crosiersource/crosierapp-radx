<?php

namespace App\Form\Financeiro;

use App\Entity\Financeiro\Movimentacao;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MovimentacaoType.
 *
 * Form para Movimentações contendo todos os campos.
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class MovimentacaoType extends AbstractType
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

            $form->remove('cedente');
            $form->remove('sacado');
            $this->movimentacaoTypeBuilder->build($form, $movimentacao);
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                $cedente = isset($event->getData()['cedente']) && $event->getData()['cedente'] ? $event->getData()['cedente'] : null;
                $form->remove('cedente');
                $form->add('cedente', ChoiceType::class, array(
                    'label' => 'Cedente',
                    'required' => false,
                    'choices' => [$cedente]
                ));

                $sacado = isset($event->getData()['sacado']) && $event->getData()['sacado'] ? $event->getData()['sacado'] : null;
                $form->remove('sacado');
                $form->add('sacado', ChoiceType::class, array(
                    'label' => 'Sacado',
                    'required' => false,
                    'choices' => [$sacado]
                ));
            }
        );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Movimentacao::class
        ));
    }
}