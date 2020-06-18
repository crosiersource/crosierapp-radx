<?php

namespace App\Form\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\RomaneioItem;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class RomaneioItemType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    /**
     * @required
     * @param EntityManagerInterface $doctrine
     */
    public function setDoctrine(EntityManagerInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('est_pedidocompra_item_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            $builder = $event->getForm();
            /** @var RomaneioItem $item */
            $item = $event->getData();

            $builder->add('id', HiddenType::class, array(
                'label' => 'Código',
                'required' => false
            ));

            $builder->add('qtde', NumberType::class, [
                'label' => 'Qtde',
                'grouping' => 'true',
                'scale' => 3,
                'attr' => [
                    'class' => 'crsr-dec3 focusOnReady'
                ],
                'required' => true
            ]);

            $builder->add('descricao', TextType::class, array(
                'label' => 'Descrição',
                'required' => true,
            ));

            $builder->add('precoCusto', MoneyType::class, [
                'label' => 'Preço de Custo',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => true,
                'attr' => [
                    'class' => 'crsr-money'
                ]
            ]);


            $builder->add('total', MoneyType::class, [
                'label' => 'Total',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true
            ]);

            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $item->jsonData]);

        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();

                if (isset($event->getData()['produto']) && $event->getData()['produto']) {
                    $produtoId = $event->getData()['produto'];
                    /** @var ProdutoRepository $repoProduto */
                    $repoProduto = $this->doctrine->getRepository(Produto::class);
                    $produto = $repoProduto->find($produtoId);
                    $produtoChoices = [$produto];
                    $form->remove('produto');
                    $form->add('produto', EntityType::class, [
                        'class' => Produto::class,
                        'choices' => $produtoChoices,
                        'data' => $produto ?? null
                    ]);
                }

            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => RomaneioItem::class
        ));
    }
}