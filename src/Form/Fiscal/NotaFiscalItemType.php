<?php

namespace App\Form\Fiscal;

use CrosierSource\CrosierLibRadxBundle\Business\Fiscal\NotaFiscalBusiness;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibRadxBundle\Entity\Fiscal\NotaFiscalItem;
use Doctrine\ORM\EntityManagerInterface;
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
 *
 */
class NotaFiscalItemType extends AbstractType
{

    private NotaFiscalBusiness $notaFiscalBusiness;

    /**
     * @required
     * @param NotaFiscalBusiness $notaFiscalBusiness
     */
    public function setNotaFiscalBusiness(NotaFiscalBusiness $notaFiscalBusiness): void
    {
        $this->notaFiscalBusiness = $notaFiscalBusiness;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var NotaFiscalItem $notaFiscalItem */
            $notaFiscalItem = $event->getData();
            $builder = $event->getForm();

            $disabled = false;
            if ($notaFiscalItem->notaFiscal) {
                if (!$this->notaFiscalBusiness->permiteSalvar($notaFiscalItem->notaFiscal)) {
                    $disabled = true;
                }
                if ($notaFiscalItem->notaFiscal->documentoEmitente && !($this->notaFiscalBusiness->isCnpjEmitente($notaFiscalItem->notaFiscal->documentoEmitente))) {
                    $disabled = true;
                }
            }

            $builder->add('codigo', TextType::class, [
                'label' => 'Código',
                'required' => true,
                'attr' => ['class' => 'focusOnReady'],
                'disabled' => $disabled
            ]);

            $builder->add('descricao', TextType::class, [
                'label' => 'Descrição',
                'required' => true,
                'attr' => [
                    'maxlength' => '120'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('cfop', TextType::class, [
                'label' => 'CFOP',
                'required' => true,
                'disabled' => $disabled
            ]);

            $builder->add('csosn', IntegerType::class, [
                'label' => 'CSOSN',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('ncm', TextType::class, [
                'label' => 'NCM',
                'required' => true,
                'disabled' => $disabled
            ]);

            $builder->add('cest', TextType::class, [
                'label' => 'CEST',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('cst', TextType::class, [
                'label' => 'CST',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('qtde', NumberType::class, [
                'label' => 'Qtde',
                'grouping' => 'true',
                'scale' => 3,
                'attr' => [
                    'class' => 'crsr-dec3'
                ],
                'required' => true,
                'disabled' => $disabled
            ]);

            $builder->add('unidade', TextType::class, [
                'label' => 'Unidade',
                'required' => true,
                'disabled' => $disabled
            ]);


            $builder->add('icmsValor', MoneyType::class, [
                'label' => 'ICMS Valor',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('icmsModBC', TextType::class, [
                'label' => 'ICMS Mód BC',
                'required' => false,
                'disabled' => $disabled
            ]);

            $builder->add('icmsAliquota', NumberType::class, [
                'label' => 'ICMS Aliq',
                'scale' => 2,
                'help' => 'Em %',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-dec2'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('icmsValorBc', MoneyType::class, [
                'label' => 'ICMS BC',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);


            $builder->add('pisValor', MoneyType::class, [
                'label' => 'PIS Valor',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('pisAliquota', NumberType::class, [
                'label' => 'PIS Aliq',
                'scale' => 2,
                'help' => 'Em %',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-dec2'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('pisValorBc', MoneyType::class, [
                'label' => 'PIS BC',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);


            $builder->add('cofinsValor', MoneyType::class, [
                'label' => 'COFINS Valor',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('cofinsAliquota', NumberType::class, [
                'label' => 'COFINS Aliq',
                'scale' => 2,
                'help' => 'Em %',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-dec2'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('cofinsValorBc', MoneyType::class, [
                'label' => 'COFINS BC',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);


            $builder->add('valorUnit', MoneyType::class, [
                'label' => 'Valor Unit',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => true,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('subtotal', MoneyType::class, [
                'label' => 'Subtotal',
                'currency' => 'BRL',
                'grouping' => 'true',
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true
            ]);

            $builder->add('valorDesconto', MoneyType::class, [
                'label' => 'Valor Desconto',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => $disabled
            ]);

            $builder->add('valorTotal', MoneyType::class, [
                'label' => 'Valor Total',
                'currency' => 'BRL',
                'grouping' => 'true',
                'required' => false,
                'attr' => [
                    'class' => 'crsr-money'
                ],
                'disabled' => true
            ]);

        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NotaFiscalItem::class
        ]);
    }
}