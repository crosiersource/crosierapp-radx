<?php

namespace App\Form\Vendas;

use App\Entity\Relatorios\RelCliente01;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Financeiro\Movimentacao;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\PlanoPagto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class VendaType extends AbstractType
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
            /** @var Venda $venda */
            $venda = $event->getData();
            $builder = $event->getForm();

            $builder->add('id', IntegerType::class, [
                'label' => 'Id',
                'required' => false,
                'attr' => ['readonly' => 'readonly']
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
                'disabled' => true
            ]);

            $builder->add('status', TextType::class, [
                'label' => 'Status',
                'required' => false,
                'attr' => ['readonly' => 'readonly']
            ]);

            $modoChoices = $choices['planosPagto'] ?? $this->doctrine->getRepository(PlanoPagto::class)
                    ->findByFiltersSimpl([['ativo', 'EQ', true]], ['codigo' => 'ASC']);

            $builder->add('planoPagto', EntityType::class, [
                'label' => 'Modo',
                'class' => PlanoPagto::class,
                'placeholder' => '...',
                'choices' => $modoChoices,
                'empty_data' => 0,
                'choice_label' => function (?PlanoPagto $planoPagto) {
                    return $planoPagto ? $planoPagto->codigo . ' - ' . $planoPagto->descricao : null;
                },
                'required' => false,
                'attr' => ['class' => 'autoSelect2 focusOnReady']
            ]);

            $clientes = [null];
            if ($venda->cliente) {
                $clientes = [$venda->cliente];
            }
            $builder->add('cliente', EntityType::class, [
                'label' => 'Cliente',
                'class' => Cliente::class,
                'choices' => $clientes,
                'data' => $venda->cliente ?? null,
                'choice_name' => function (?Cliente $cliente) {
                    return $cliente ? $cliente->getId() : null;
                },
                'choice_label' => function (?Cliente $cliente) {
                    return $cliente ? $cliente->nome : null;
                },
                'attr' => [
                    'data-route-url' => '/crm/cliente/findClienteByStr/',
                    'data-val' => $venda->cliente ? $venda->cliente->getId() : '',
                    'class' => 'autoSelect2'
                ],
                'required' => false,
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


            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $venda->jsonData]);

        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {

                $builder = $event->getForm();
                $data = $event->getData();

                $cliente = null;
                $clienteId = $data['cliente'] ?? null;
                /** @var ClienteRepository $repoCliente */
                $repoCliente = $this->doctrine->getRepository(Cliente::class);
                $cliente = $repoCliente->find($clienteId);
                $builder->remove('cliente');
                $builder->add('cliente', EntityType::class, [
                    'class' => Cliente::class,
                    'data' => $cliente ?? null,
                    'choice_label' => function (?Cliente $cliente) {
                        return $cliente ? $cliente->nome : null;
                    },
                    'choices' => [$cliente]
                ]);

                $builder->remove('jsonData');
                $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $data['jsonData'] ?? null]);

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