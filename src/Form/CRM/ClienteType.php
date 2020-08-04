<?php

namespace App\Form\CRM;


use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class ClienteType extends AbstractType
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
        $jsonMetadata = json_decode($repoAppConfig->findByChave('crm_cliente_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var Cliente $cliente */
            $cliente = $event->getData();
            $builder = $event->getForm();

            $builder->add('id', TextType::class, [
                'label' => 'Id',
                'required' => false,
                'attr' => ['readonly' => 'readonly']
            ]);

            $builder->add('documento', TextType::class, [
                'label' => 'CPF/CNPJ',
                'required' => false,
                'attr' => [
                    'class' => 'cpfCnpj'
                ],
            ]);

            $builder->add('nome', TextType::class, [
                'label' => 'Nome',
                'attr' => ['class' => 'focusOnReady']
            ]);


            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $cliente->jsonData]);

        });


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {

                $builder = $event->getForm();
                $data = $event->getData();

                $builder->remove('jsonData');
                $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $data['jsonData'] ?? null]);

            }
        );

        // Necessário para os casos onde o formulário não tem todos os campos do json_data (para que eles não desapareçam por conta disto)
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {
                /** @var Cliente $cliente */
                $cliente = $event->getData();
                if ($cliente->getId()) {
                    $jsonDataOrig = json_decode($this->doctrine->getConnection()->fetchAssoc('SELECT json_data FROM crm_cliente WHERE id = :id', ['id' => $cliente->getId()])['json_data'] ?? '{}', true);
                    $cliente->jsonData = array_merge($jsonDataOrig, $cliente->jsonData);
                    $event->setData($cliente);
                }
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cliente::class
        ]);
    }
}