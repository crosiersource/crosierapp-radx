<?php

namespace App\Form\Financeiro;

use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\CentroCusto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CentroCustoType
 *
 * @package App\Form\Financeiro
 * @author Carlos Eduardo Pauluk
 */
class CentroCustoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('codigo', IntegerType::class, [
            'label' => 'Código'
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CentroCusto::class
        ]);
    }
}