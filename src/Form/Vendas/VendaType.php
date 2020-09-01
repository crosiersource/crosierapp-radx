<?php

namespace App\Form\Vendas;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\RH\Colaborador;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class VendaType extends AbstractType
{

    private EntityManagerInterface $doctrine;

    private Security $security;

    public function __construct(EntityManagerInterface $doctrine, Security $security)
    {
        $this->doctrine = $doctrine;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $jsonMetadata = json_decode($repoAppConfig->findByChave('ven_venda_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var Venda $venda */
            $venda = $event->getData();
            $builder = $event->getForm();

            $disabled = $venda->getId() && ($venda->status !== 'PV ABERTO');

            $builder->add('id', IntegerType::class, [
                'label' => 'Id',
                'required' => true,
                'attr' => ['readonly' => 'readonly'],
                'disabled' => $disabled
            ]);

            $builder->add('dtVenda', DateTimeType::class, [
                'label' => 'Dt Venda',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => [
                    'class' => 'crsr-datetime'
                ],
                'required' => true,
                'disabled' => $disabled || !($this->security->isGranted('ROLE_ESTOQUE_ADMIN'))
            ]);

            $builder->add('status', TextType::class, [
                'label' => 'Status',
                'required' => false,
                'attr' => ['readonly' => 'readonly'],
                'disabled' => $disabled
            ]);


            $vendedorChoices = $this->doctrine->getRepository(Colaborador::class)
                ->findByFiltersSimpl([['atual', 'EQ', true]], ['nome' => 'ASC']);

            $builder->add('vendedor', EntityType::class, [
                'label' => 'Vendedor',
                'class' => Colaborador::class,
                'placeholder' => '...',
                'choices' => $vendedorChoices,
                'choice_label' => function (?Colaborador $colaborador) {
                    return $colaborador ? str_pad($colaborador->getId(), 3, '0', STR_PAD_LEFT) . ' - ' . $colaborador->nome : null;
                },
                'required' => false,
                'attr' => ['class' => 'autoSelect2 ' . (!$venda->getId() ? 'focusOnReady' : '')],
                'disabled' => $disabled
            ]);


            $builder->add('subtotal', MoneyType::class, [
                'label' => 'Subtotal',
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

            $builder->add('valorTotal', MoneyType::class, [
                'label' => 'Valor Total',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true,
                'required' => false
            ]);


            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $venda->jsonData, 'disabled' => $disabled]);

        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {

                $builder = $event->getForm();
                $data = $event->getData();

                $builder->remove('jsonData');
                $builder->add('jsonData', JsonType::class,
                    [
                        'jsonMetadata' => $jsonMetadata,
                        'jsonData' => ($data['jsonData'] ?? null)
                    ]);

            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Venda::class
        ]);
    }
}
