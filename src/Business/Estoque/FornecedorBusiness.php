<?php

namespace App\Business\Estoque;

use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Fornecedor;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\FornecedorTipo;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Pessoa;
use Doctrine\ORM\EntityManagerInterface;

class FornecedorBusiness
{

    /** @var EntityManagerInterface */
    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function parseFormData(&$formData)
    {

        foreach ($formData as $key => $value) {
            if ($value == '') {
                $formData[$key] = null;
            }
        }

        $formData['codigo'] = (isset($formData['codigo']) and $formData['codigo'] > 0) ? $formData['codigo'] : null;
        $formData['cpf'] = (isset($formData['cpf']) and $formData['cpf'] !== null) ? preg_replace("/[^0-9]/", "", $formData['cpf']) : null;
        $formData['cnpj'] = (isset($formData['cnpj']) and $formData['cnpj'] !== null) ? preg_replace("/[^0-9]/", "", $formData['cnpj']) : null;

    }

    /**
     * Transforma um Fornecedor em um array para manipulação no FornecedorType.
     * @param Fornecedor $fornecedor
     * @return array
     */
    public function fornecedor2FormData(Fornecedor $fornecedor)
    {
        $formData = array();

        // Campos gerais (tanto para PESSOA_FISICA quanto para PESSOA_JURIDICA)

        $formData['id'] = $fornecedor->getId();

        $formData['tipoPessoa'] = $fornecedor->getPessoa()->getTipoPessoa();
        $formData['pessoa_id'] = $fornecedor->getPessoa()->getId();
        $formData['codigo'] = $fornecedor->getCodigo();
        $formData['fone1'] = $fornecedor->getFone1();
        $formData['fone2'] = $fornecedor->getFone2();
        $formData['fone3'] = $fornecedor->getFone3();
        $formData['fone4'] = $fornecedor->getFone4();
        $formData['email'] = $fornecedor->getEmail();
        $formData['obs'] = $fornecedor->getObs();

        $formData['tipo'] = $fornecedor->getTipo();


        if ($fornecedor->getPessoa()->getTipoPessoa() == 'PESSOA_FISICA') {

            // Campos para PESSOA_FISICA

            $formData['cpf'] = $fornecedor->getPessoa()->getDocumento();
            $formData['nome'] = $fornecedor->getPessoa()->getNome();
            $formData['rg'] = $fornecedor->getRg();
            $formData['dtEmissaoRg'] = ($fornecedor->getDtEmissaoRg() instanceof \DateTime) ? $fornecedor->getDtEmissaoRg()->format('d/m/Y') : null;
            $formData['orgaoEmissorRg'] = $fornecedor->getOrgaoEmissorRg();
            $formData['estadoRg'] = $fornecedor->getEstadoRg();
            $formData['sexo'] = $fornecedor->getSexo();
            $formData['naturalidade'] = $fornecedor->getNaturalidade();
            $formData['estadoCivil'] = $fornecedor->getEstadoCivil();
        } else {

            // Campos para PESSOA_JURIDICA

            $formData['cnpj'] = $fornecedor->getPessoa()->getDocumento();
            $formData['razaoSocial'] = $fornecedor->getPessoa()->getNome();
            $formData['nomeFantasia'] = $fornecedor->getPessoa()->getNomeFantasia();
            $formData['inscricaoEstadual'] = $fornecedor->getInscricaoEstadual();
            $formData['inscricaoMunicipal'] = $fornecedor->getInscricaoMunicipal();
            $formData['contato'] = $fornecedor->getContato();
            $formData['website'] = $fornecedor->getWebsite();
        }

        return $formData;

    }

    /**
     * Converte um array do FornecedorType para um Fornecedor.
     *
     * @param $formData
     * @return Fornecedor|null|object
     * @throws \Exception
     */
    public function formData2Fornecedor($formData)
    {
        if (isset($formData['id'])) {
            $fornecedor = $this->doctrine->getRepository(Fornecedor::class)->find($formData['id']);
            if (!$fornecedor) {
                $fornecedor = new Fornecedor();
                $fornecedor->setPessoa(new Pessoa());
            } else {
                $pessoa = $this->doctrine->getRepository(Pessoa::class)->find($formData['pessoa_id']);
                if (!$pessoa) {
                    throw new \Exception("Pessoa não encontrada.");
                }
                $fornecedor->setPessoa($pessoa);
            }
        } else {
            $fornecedor = new Fornecedor();
            $fornecedor->setPessoa(new Pessoa());
        }

        if (isset($formData['tipo'])) {
            $tipo = $this->doctrine->getRepository(FornecedorTipo::class)->find($formData['tipo']);
            $fornecedor->setTipo($tipo);
        }

        $fornecedor->getPessoa()->setTipoPessoa($formData['tipoPessoa']);

        $fornecedor->setCodigo($formData['codigo']);
        $fornecedor->setFone1($formData['fone1']);
        $fornecedor->setFone2($formData['fone2']);
        $fornecedor->setFone3($formData['fone3']);
        $fornecedor->setFone4($formData['fone4']);
        $fornecedor->setEmail($formData['email']);
        $fornecedor->setObs($formData['obs']);

        if ($fornecedor->getPessoa()->getTipoPessoa() == 'PESSOA_FISICA') {

            // Campos para PESSOA_FISICA

            $fornecedor->getPessoa()->setDocumento($formData['cpf']);
            $fornecedor->getPessoa()->setNome($formData['nome']);
            $fornecedor->setRg($formData['rg']);
            $fornecedor->setDtEmissaoRg($formData['dtEmissaoRg']);
            $fornecedor->setEstadoRg($formData['estadoRg']);
            $fornecedor->setSexo($formData['sexo']);
            $fornecedor->setNaturalidade($formData['naturalidade']);
            $fornecedor->setEstadoCivil($formData['estadoCivil']);
        } else {

            // Campos para PESSOA_JURIDICA

            $fornecedor->getPessoa()->setDocumento($formData['cnpj']);
            $fornecedor->getPessoa()->setNome($formData['razaoSocial']);
            $fornecedor->getPessoa()->setNomeFantasia($formData['nomeFantasia']);
            $fornecedor->setInscricaoEstadual($formData['inscricaoEstadual']);
            $fornecedor->setInscricaoMunicipal($formData['inscricaoMunicipal']);
            $fornecedor->setContato($formData['contato']);
            $fornecedor->setWebsite($formData['website']);
        }

        return $fornecedor;
    }
}