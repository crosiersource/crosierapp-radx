<?php

namespace App\Form\Estoque;

use App\Entity\Estoque\ProdutoImagem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ProdutoImagemType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', HiddenType::class, [
            'required' => false
        ]);

        $builder->add('descricao', TextType::class, [
            'label' => 'Descrição',
            'attr' => ['class' => 'focusOnReady notuppercase'],
            'required' => false
        ]);

        $builder->add('imageFile', FileType::class, [
            'label' => 'Imagem',
            'required' => false
        ]);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProdutoImagem::class
        ]);
    }
}