<?php

namespace App\Form\Fiscal;

use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalCartaCorrecao;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalCartaCorrecaoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('cartaCorrecao', TextareaType::class, array(
            'label' => 'Mensagem',
            'attr' => ['class' => 'notuppercase'],
            'required' => true
        ));

        $builder->add('dtCartaCorrecao', DateTimeType::class, array(
            'label' => 'Data/Hora',
            'widget' => 'single_text',
            'required' => true,
            'html5' => false,
            'format' => 'dd/MM/yyyy HH:mm:ss',
            'attr' => [
                'class' => 'crsr-datetime'
            ]
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NotaFiscalCartaCorrecao::class
        ));
    }
}