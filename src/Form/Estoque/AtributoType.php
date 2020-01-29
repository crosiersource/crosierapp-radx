<?php

namespace App\Form\Estoque;

use App\Entity\Estoque\Atributo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class AtributoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('UUID', TextType::class, [
            'label' => 'UUID',
            'attr' => ['readonly' => true, 'class' => 'notuppercase'],
            'required' => true
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'attr' => ['class' => 'focusOnReady'],
        ]);

        $builder->add('label', TextType::class, [
            'label' => 'Label',
            'attr' => ['class' => 'notuppercase'],
        ]);

        $builder->add('tipo', ChoiceType::class, [
            'label' => 'Tipo',
            'choices' => [
                'UP' => 'UP',
                'STRING' => 'STRING',
                'HTML' => 'HTML',
                'INTEGER' => 'INTEGER',
                'DECIMAL1' => 'DECIMAL1',
                'DECIMAL2' => 'DECIMAL2',
                'DECIMAL3' => 'DECIMAL3',
                'DECIMAL4' => 'DECIMAL4',
                'DECIMAL5' => 'DECIMAL5',
                'DATE' => 'DATE',
                'DATETIME' => 'DATETIME',
                'COMPO' => 'COMPO',
                'LISTA' => 'LISTA',
                'TAGS' => 'TAGS',
                'S/N' => 'S/N',
            ],
            'attr' => ['class' => 'autoSelect2']
        ]);

        $builder->add('config', TextType::class, [
            'label' => 'Configuração',
            'attr' => ['class' => 'notuppercase'],
            'required' => false
        ]);

        $builder->add('prefixo', TextType::class, [
            'label' => 'Prefixo',
            'attr' => ['class' => 'notuppercase'],
            'required' => false
        ]);

        $builder->add('sufixo', TextType::class, [
            'label' => 'Sufixo',
            'attr' => ['class' => 'notuppercase'],
            'required' => false
        ]);

        $builder->add('primaria', ChoiceType::class, [
            'label' => 'Primária',
            'expanded' => true,
            'choices' => [
                'SIM' => 'S',
                'NÃO' => 'N',
            ],
        ]);

        $builder->add('ordem', IntegerType::class, [
            'label' => 'Ordem',
            'required' => false
        ]);

        $builder->add('visivel', ChoiceType::class, [
            'label' => 'Visível',
            'expanded' => true,
            'choices' => [
                'SIM' => 'S',
                'NÃO' => 'N',
            ],
        ]);

        $builder->add('editavel', ChoiceType::class, [
            'label' => 'Editável',
            'expanded' => true,
            'choices' => [
                'SIM' => 'S',
                'NÃO' => 'N',
            ],
        ]);


        $builder->add('obs', TextareaType::class, [
            'label' => 'Obs',
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Atributo::class
        ));
    }
}