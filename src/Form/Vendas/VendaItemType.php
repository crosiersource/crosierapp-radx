<?php

namespace App\Form\Vendas;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
 */
class VendaItemType extends AbstractType
{

    /** @var EntityManagerInterface */
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('ven_venda_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var VendaItem $vendaItem */
            $vendaItem = $event->getData();
            $builder = $event->getForm();

            $builder->add('id', IntegerType::class, [
                'label' => 'Id',
                'required' => false,
                'attr' => ['readonly' => 'readonly']
            ]);

            $builder->add('qtde', NumberType::class, [
                'label' => 'Qtde',
                'grouping' => 'true',
                'scale' => 3,
                'attr' => [
                    'class' => 'crsr-dec3 focusOnReady'
                ],
                'required' => true
            ]);

            $builder->add('descricao', TextType::class, [
                'label' => 'Produto',
                'required' => false,
            ]);

            $produtos = [null];
            if ($vendaItem->produto) {
                $produtos = [$vendaItem->produto];
            }
            $builder->add('produto', EntityType::class, [
                'label' => 'Produto',
                'class' => Produto::class,
                'choices' => $produtos,
                'data' => $venda->produto ?? null,
                'choice_name' => function (?Produto $produto) {
                    return $produto ? $produto->getId() : null;
                },
                'choice_label' => function (?Produto $produto) {
                    return $produto ? $produto->nome : null;
                },
                'attr' => [
                    'data-route-url' => '/est/produto/findProdutoByIdOuNome',
                    'class' => 'autoSelect2'
                ],
                'required' => false,
            ]);


            $builder->add('precoVenda', MoneyType::class, [
                'label' => 'Vlr Un',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'required' => false,
                'disabled' => true
            ]);

            $builder->add('desconto', MoneyType::class, [
                'label' => 'Descontos',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true,
                'required' => false
            ]);

            $builder->add('subtotal', MoneyType::class, [
                'label' => 'Subtotal',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true,
                'required' => false
            ]);

            $builder->add('total', MoneyType::class, [
                'label' => 'Total',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true,
                'required' => false
            ]);


            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $vendaItem->jsonData]);

        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {

                $builder = $event->getForm();
                $data = $event->getData();

                $produto = null;
                $produtoId = $data['produto'] ?? null;
                /** @var ProdutoRepository $repoProduto */
                $repoProduto = $this->doctrine->getRepository(Produto::class);
                $produto = $repoProduto->find($produtoId);
                $builder->remove('produto');
                $builder->add('produto', EntityType::class, [
                    'class' => Produto::class,
                    'data' => $produto ?? null,
                    'choice_label' => function (?Produto $produto) {
                        return $produto ? $produto->nome : null;
                    },
                    'choices' => [$produto]
                ]);

                $builder->remove('jsonData');
                $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $data['jsonData'] ?? null]);

            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VendaItem::class
        ]);
    }
}