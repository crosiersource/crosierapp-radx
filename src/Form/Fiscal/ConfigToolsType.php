<?php

namespace App\Form\Fiscal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

/**
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ConfigToolsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id', TextType::class, [
            'label' => 'Id',
            'required' => true,
            'disabled' => true
        ]);


        $builder->add('atualizacao', DateTimeType::class, [
            'label' => 'Atualização',
            'widget' => 'single_text',
            'required' => true,
            'html5' => false,
            'format' => 'dd/MM/yyyy HH:mm:ss',
            'attr' => [
                'class' => 'crsr-datetime'
            ]
        ]);

        $builder->add('tpAmb', ChoiceType::class, [
            'label_attr' => [
                'class' => 'radio-inline'
            ],
            'choices' => [
                'Produção' => 1,
                'Homologação' => 2
            ],
            'attr' => [
                'class' => 'autoSelect2'
            ]
        ]);

        $builder->add('serie_NFE_PROD', TextType::class, [
            'label' => 'Série NFe (PROD)',
            'required' => true,
        ]);

        $builder->add('serie_NFCE_PROD', TextType::class, [
            'label' => 'Série NFCe (PROD)',
            'required' => true,
        ]);

        $builder->add('serie_NFE_HOM', TextType::class, [
            'label' => 'Série NFe (HOM)',
            'required' => true,
        ]);

        $builder->add('serie_NFCE_HOM', TextType::class, [
            'label' => 'Série NFCe (HOM)',
            'required' => true,
        ]);

        $builder->add('cnpj', TextType::class, [
            'label' => 'CNPJ',
            'required' => true,
            'attr' => [
                'class' => 'cnpj'
            ]
        ]);

        $builder->add('razaosocial', TextType::class, array(
            'label' => 'Razão Social',
            'required' => true
        ));

        $builder->add('ie', TextType::class, array(
            'label' => 'Inscrição Estadual',
            'required' => false
        ));

        $builder->add('enderecoCompleto', TextType::class, array(
            'label' => 'Endereço Completo',
            'required' => false
        ));


        $builder->add('enderEmit_xLgr', TextType::class, array(
            'label' => 'Logradouro (Emitente)',
            'required' => false
        ));

        $builder->add('enderEmit_nro', TextType::class, array(
            'label' => 'Número (Emitente)',
            'required' => false
        ));

        $builder->add('enderEmit_xBairro', TextType::class, array(
            'label' => 'Bairro (Emitente)',
            'required' => false
        ));

        $builder->add('enderEmit_cep', TextType::class, array(
            'label' => 'CEP (Emitente)',
            'required' => false,
            'attr' => [
                'class' => 'cep'
            ]
        ));

        $builder->add('telefone', TextType::class, array(
            'label' => 'Telefone',
            'required' => false
        ));

        $builder->add('siglaUF', ChoiceType::class, array(
            'label' => 'Estado',
            'choices' => [
                'Acre' => 'AC',
                'Alagoas' => 'AL',
                'Amapá' => 'AP',
                'Amazonas' => 'AM',
                'Bahia' => 'BA',
                'Ceará' => 'CE',
                'Distrito Federal' => 'DF',
                'Espírito Santo' => 'ES',
                'Goiás' => 'GO',
                'Maranhão' => 'MA',
                'Mato Grosso' => 'MT',
                'Mato Grosso do Sul' => 'MS',
                'Minas Gerais' => 'MG',
                'Pará' => 'PA',
                'Paraíba' => 'PB',
                'Paraná' => 'PR',
                'Pernambuco' => 'PE',
                'Piauí' => 'PI',
                'Rio de Janeiro' => 'RJ',
                'Rio Grande do Norte' => 'RN',
                'Rio Grande do Sul' => 'RS',
                'Rondônia' => 'RO',
                'Roraima' => 'RR',
                'Santa Catarina' => 'SC',
                'São Paulo' => 'SP',
                'Sergipe' => 'SE',
                'Tocantins' => 'TO'
            ],
            'required' => true,
            'attr' => [
                'class' => 'autoSelect2'
            ]
        ));

        $builder->add('schemes', TextType::class, array(
            'label' => 'Schemes',
            'required' => true
        ));

        $builder->add('versao', TextType::class, array(
            'label' => 'Versão',
            'required' => true
        ));

        $builder->add('tokenIBPT', TextType::class, array(
            'label' => 'Token IBPT',
            'required' => false
        ));

        $builder->add('CSC_prod', TextType::class, array(
            'label' => 'CSC (PROD)',
            'required' => true
        ));

        $builder->add('CSCid_prod', TextType::class, array(
            'label' => 'CSC Id (PROD)',
            'required' => true
        ));

        $builder->add('CSC_hom', TextType::class, array(
            'label' => 'CSC (HOM)',
            'required' => true
        ));

        $builder->add('CSCid_hom', TextType::class, array(
            'label' => 'CSC Id (HOM)',
            'required' => true
        ));


        $builder->add('certificado', FileType::class, [
            'label' => 'Certificado',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/x-pkcs12',
                        'application/octet-stream'
                    ]
                ])
            ],
        ]);

        $builder->add('certificadoPwd', PasswordType::class, array(
            'label' => 'Senha',
            'required' => false
        ));

    }

}