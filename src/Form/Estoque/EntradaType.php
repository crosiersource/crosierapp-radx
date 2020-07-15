<?php

namespace App\Form\Estoque;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Form\JsonType;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Entrada;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class EntradaType extends AbstractType
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
        $jsonMetadata = json_decode($repoAppConfig->findByChave('est_entrada_json_metadata'), true);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($jsonMetadata) {
            /** @var Entrada $entrada */
            $entrada = $event->getData();
            $builder = $event->getForm();

            $builder->add('id', IntegerType::class, [
                'label' => 'Id',
                'required' => true,
                'attr' => ['readonly' => 'readonly']
            ]);

            $builder->add('descricao', TextType::class, [
                'label' => 'Descrição',
                'required' => true,
            ]);

            $builder->add('dtLote', DateTimeType::class, [
                'label' => 'Dt Lote',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => [
                    'class' => 'crsr-datetime',
                    'readonly' => 'readonly'
                ],
                'required' => true,
            ]);

            $builder->add('status', TextType::class, [
                'label' => 'Status',
                'required' => true,
                'attr' => ['readonly' => 'readonly']
            ]);

            $builder->add('responsavel', TextType::class, [
                'label' => 'Responsável',
                'required' => true,
                'attr' => ['readonly' => 'readonly']
            ]);

            $builder->add('dtIntegracao', DateTimeType::class, [
                'label' => 'Dt Integração',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'attr' => [
                    'class' => 'crsr-datetime',
                    'readonly' => 'readonly'
                ],
                'required' => true,
            ]);


            $builder->add('jsonData', JsonType::class, ['jsonMetadata' => $jsonMetadata, 'jsonData' => $entrada->jsonData]);

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


        // Necessário para os casos onde o formulário não tem todos os campos do json_data (para que eles não desapareçam por conta disto)
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($jsonMetadata) {
                /** @var Entrada $entrada */
                $entrada = $event->getData();
                if ($entrada->getId()) {
                    $jsonDataOrig = json_decode($this->doctrine->getConnection()->fetchAssoc('SELECT json_data FROM est_entrada WHERE id = :id', ['id' => $entrada->getId()])['json_data'] ?? '{}', true);
                    $entrada->jsonData = array_merge($jsonDataOrig, $entrada->jsonData);
                    $event->setData($entrada);
                }
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entrada::class
        ]);
    }
}