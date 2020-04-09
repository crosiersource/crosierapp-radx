<?php


namespace App\Business\ECommerce;

use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Produto;
use App\Entity\Estoque\Subgrupo;
use App\EntityHandler\Estoque\DeptoEntityHandler;
use App\EntityHandler\Estoque\GrupoEntityHandler;
use App\EntityHandler\Estoque\ProdutoEntityHandler;
use App\EntityHandler\Estoque\SubgrupoEntityHandler;
use App\Repository\Estoque\DeptoRepository;
use App\Repository\Estoque\GrupoRepository;
use App\Repository\Estoque\SubgrupoRepository;
use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Regras de negócio para a integração com a WebStorm.
 *
 * Class IntegraWebStorm
 * @package App\Business\ECommerce
 * @author Carlos Eduardo Pauluk
 */
class IntegraWebStorm extends BaseBusiness
{

    private string $chave;

    private \nusoap_client $nusoapClientExportacao;

    private \nusoap_client $nusoapClientImportacao;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private Security $security;

    private AppConfigRepository $repoAppConfig;

    private DeptoEntityHandler $deptoEntityHandler;

    private GrupoEntityHandler $grupoEntityHandler;

    private SubgrupoEntityHandler $subgrupoEntityHandler;

    private ProdutoEntityHandler $produtoEntityHandler;

    private UploaderHelper $uploaderHelper;

    private ?array $tiposCaracteristicasNaWebStorm = null;

    private ?array $deptosNaWebStorm = null;

    private ?array $marcasNaWebStorm = null;

    public function __construct(AppConfigEntityHandler $appConfigEntityHandler,
                                Security $security,
                                DeptoEntityHandler $deptoEntityHandler,
                                GrupoEntityHandler $grupoEntityHandler,
                                SubgrupoEntityHandler $subgrupoEntityHandler,
                                ProdutoEntityHandler $produtoEntityHandler,
                                UploaderHelper $uploaderHelper)
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->security = $security;
        try {
            /** @var AppConfigRepository $repoAppConfig */
            $this->repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
            /** @var AppConfig $appConfigChave */
            $appConfigChave = $this->repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_WEBSTORM_chave'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfigChave) {
                throw new \LogicException('ecomm_info_integra_WEBSTORM_chave N/D');
            }
            $this->chave = $appConfigChave->getValor();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao instanciar IntegraWebStorm (chave ecomm_info_integra_WEBSTORM_chave ?)');
        }
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->uploaderHelper = $uploaderHelper;
    }


    /**
     * Obtém as marcas cadastradas na WebStorm
     * @return array
     */
    public function selectMarcasNaWebStorm(): array
    {
        if (!$this->marcasNaWebStorm) {
            $client = $this->getNusoapClientExportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>TYzIFWVpnWzZVbKZjVtBnWXRkRWRVM=A3SWVUNPJVbxM1UxoVWWJDN4ZlRB1</chave>
                    <acao>select</acao>
                    <modulo>registros</modulo>    
                    <filtro>
                           <departamento></departamento>
                           <tipoAtributo></tipoAtributo>
                           <atributo></atributo>
                           <tipoCaracteristica></tipoCaracteristica>
                           <caracteristica></caracteristica>
                           <marca>1</marca>
                    </filtro>
                    </ws_integracao>]]>';

            $arResultado = $client->call('registrosSelect', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                throw new \RuntimeException($xmlResult->erros->erro->__toString());
            }

            $this->marcasNaWebStorm = [];
            foreach ($xmlResult->registros->marcas->marca as $marca) {
                $this->marcasNaWebStorm[(int)$marca->idMarca->__toString()] = [
                    'nome' => $marca->nome->__toString(),
                ];
            }
        }

        return $this->marcasNaWebStorm;
    }


    /**
     * @param string $marca
     * @return int
     * @throws ViewException
     */
    private function integraMarca(string $marca): int
    {
        $marcasNaWebStorm = $this->selectMarcasNaWebStorm();

        $idMarcaNaWebStorm = null;

        foreach ($marcasNaWebStorm as $id => $marcaNaWebStorm) {
            if ($marcaNaWebStorm['nome'] === $marca) {
                $idMarcaNaWebStorm = $id;
                break;
            }
        }

        if (!$idMarcaNaWebStorm) {

            $client = $this->getNusoapClientImportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>insert</acao>
               <modulo>marca</modulo>
               <marca pk="idMarca">
                  <idMarca></idMarca>
                  <nome>' . $marca . '</nome>
               </marca>
            </ws_integracao>]]>';

            $arResultado = $client->call('marcaAdd', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            $idMarcaNaWebStorm = (int)$xmlResult->idMarca->__toString();
        }

        return $idMarcaNaWebStorm;
    }

    /**
     * Obtém os tipos de características cadastrados na WebStorm
     * @return array
     */
    public function selectTiposCaracteristicasNaWebStorm(): array
    {
        if (!$this->tiposCaracteristicasNaWebStorm) {
            $client = $this->getNusoapClientExportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>TYzIFWVpnWzZVbKZjVtBnWXRkRWRVM=A3SWVUNPJVbxM1UxoVWWJDN4ZlRB1</chave>
                    <acao>select</acao>
                    <modulo>registros</modulo>    
                    <filtro>
                           <departamento></departamento>
                           <tipoAtributo></tipoAtributo>
                           <atributo></atributo>
                           <tipoCaracteristica>1</tipoCaracteristica>
                           <caracteristica></caracteristica>
                           <marca></marca>
                    </filtro>
                    </ws_integracao>]]>';

            $arResultado = $client->call('registrosSelect', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                throw new \RuntimeException($xmlResult->erros->erro->__toString());
            }

            $this->tiposCaracteristicasNaWebStorm = [];
            foreach ($xmlResult->registros->tipoCaracteristicas->tipoCaracteristica as $tipoCaracteristica) {
                $this->tiposCaracteristicasNaWebStorm[(int)$tipoCaracteristica->idTipoCaracteristica->__toString()] = [
                    'nome' => $tipoCaracteristica->nome->__toString(),
                ];
            }


            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>TYzIFWVpnWzZVbKZjVtBnWXRkRWRVM=A3SWVUNPJVbxM1UxoVWWJDN4ZlRB1</chave>
                    <acao>select</acao>
                    <modulo>registros</modulo>    
                    <filtro>
                           <departamento></departamento>
                           <tipoAtributo></tipoAtributo>
                           <atributo></atributo>
                           <tipoCaracteristica></tipoCaracteristica>
                           <caracteristica>1</caracteristica>
                           <marca></marca>
                    </filtro>
                    </ws_integracao>]]>';

            $arResultado = $client->call('registrosSelect', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                throw new \RuntimeException($xmlResult->erros->erro->__toString());
            }

            foreach ($xmlResult->registros->caracteristicas->caracteristica as $caracteristica) {
                $idTipoCaracteristica = (int)$caracteristica->idTipoCaracteristica->__toString();
                if (isset($this->tiposCaracteristicasNaWebStorm[$idTipoCaracteristica])) {
                    $this->tiposCaracteristicasNaWebStorm[$idTipoCaracteristica]['caracteristicas'][(int)$caracteristica->idCaracteristica->__toString()] = [
                        'nome' => $caracteristica->valor->__toString(),
                    ];
                }
            }


        }

        return $this->tiposCaracteristicasNaWebStorm;
    }


    /**
     * @param string $campo
     * @param string $tipoCaracteristica
     * @return int
     * @throws ViewException
     */
    private function integraTipoCaracteristica(string $campo, string $tipoCaracteristica): int
    {
        $tiposCaracteristicasNaWebStorm = $this->selectTiposCaracteristicasNaWebStorm();

        $idTipoCaracteristicaNaWebStorm = null;

        foreach ($tiposCaracteristicasNaWebStorm as $id => $tipoCaracteristicaNaWebStorm) {
            if ($tipoCaracteristicaNaWebStorm['nome'] === $tipoCaracteristica) {
                $idTipoCaracteristicaNaWebStorm = $id;
                break;
            }
        }

        if (!$idTipoCaracteristicaNaWebStorm) {

            $client = $this->getNusoapClientImportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>insert</acao>
               <modulo>tipoCaracteristica</modulo>
               <tipoCaracteristica pk="idTipoCaracteristica">
                  <idTipoCaracteristica></idTipoCaracteristica>
                  <nome>' . $tipoCaracteristica . '</nome>
               </tipoCaracteristica>
            </ws_integracao>]]>';

            $arResultado = $client->call('tipoCaracteristicaAdd', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                throw new \RuntimeException($xmlResult->erros->erro->__toString());
            }

            $idTipoCaracteristicaNaWebStorm = (int)$xmlResult->idTipoCaracteristica->__toString();

            $this->tiposCaracteristicasNaWebStorm = null; // para forçar a rebusca
        }

        /** @var AppConfig $appConfig */
        $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
        $jsonMetadata = json_decode($appConfig->getValor(), true);
        $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['ecommerce_id'] = $idTipoCaracteristicaNaWebStorm;
        $appConfig->setValor(json_encode($jsonMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->appConfigEntityHandler->save($appConfig);


        return $idTipoCaracteristicaNaWebStorm;
    }

    /**
     * @param string $campo
     * @param int $ecommerceId_tipoCaracteristica
     * @param string $caracteristica
     * @return int
     * @throws ViewException
     */
    private function integraCaracteristica(string $campo, int $ecommerceId_tipoCaracteristica, string $caracteristica): int
    {
        $tiposCaracteristicasNaWebStorm = $this->selectTiposCaracteristicasNaWebStorm();

        $idCaracteristicaNaWebStorm = null;

        foreach ($tiposCaracteristicasNaWebStorm[$ecommerceId_tipoCaracteristica]['caracteristicas'] as $id => $caracteristicaNaWebStorm) {
            if ($caracteristicaNaWebStorm['nome'] === $caracteristica) {
                $idCaracteristicaNaWebStorm = $id;
                break;
            }
        }

        if (!$idCaracteristicaNaWebStorm) {

            $client = $this->getNusoapClientImportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>insert</acao>
               <modulo>caracteristica</modulo>
               <caracteristicas>
               <caracteristica pk="idCaracteristica" pk2="idTipoCaracteristica">
                  <idCaracteristica></idCaracteristica>
                  <idTipoCaracteristica>' . $ecommerceId_tipoCaracteristica . '</idTipoCaracteristica>
                  <valor>' . $caracteristica . '</valor>
               </caracteristica>
               </caracteristicas>
            </ws_integracao>]]>';

            $arResultado = $client->call('caracteristicaAdd', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            $idCaracteristicaNaWebStorm = (int)$xmlResult->caracteristicas->caracteristica->idCaracteristica->__toString();
        }

        return $idCaracteristicaNaWebStorm;
    }

    /**
     * Percorre a árvore de Deptos/Grupos/Subgrupos e realiza a integração para o WebStorm.
     * @throws ViewException
     */
    public function integrarDeptosGruposSubgrupos()
    {
        /** @var DeptoRepository $repoDepto */
        $repoDepto = $this->getDoctrine()->getRepository(Depto::class);
        $deptos = $repoDepto->findAll(['id' => 'ASC']);
        /** @var Depto $depto */
        foreach ($deptos as $depto) {
            $this->integraDepto($depto);
            /** @var Grupo $grupo */
            foreach ($depto->grupos as $grupo) {
                $this->integraGrupo($grupo);
                /** @var Subgrupo $subgrupo */
                foreach ($grupo->subgrupos as $subgrupo) {
                    $this->integraSubgrupo($subgrupo);
                }
            }
        }
    }

    /**
     * Obtém os tipos de características cadastrados na WebStorm
     * @return array
     */
    public function selectDepartamentosNaWebStorm(): array
    {
        if (!$this->deptosNaWebStorm) {
            $client = $this->getNusoapClientExportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>TYzIFWVpnWzZVbKZjVtBnWXRkRWRVM=A3SWVUNPJVbxM1UxoVWWJDN4ZlRB1</chave>
                    <acao>select</acao>
                    <modulo>registros</modulo>    
                    <filtro>
                           <departamento>1</departamento>
                           <tipoAtributo></tipoAtributo>
                           <atributo></atributo>
                           <tipoCaracteristica></tipoCaracteristica>
                           <caracteristica></caracteristica>
                           <marca></marca>
                    </filtro>
                    </ws_integracao>]]>';

            $arResultado = $client->call('registrosSelect', [
                'xml' => utf8_decode($xml)
            ]);

            if ($client->faultcode) {
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $arResultado = utf8_encode($arResultado);
            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                throw new \RuntimeException($xmlResult->erros->erro->__toString());
            }

            $this->deptosNaWebStorm = [];

            $deptosNivel1 = [];
            $deptosNivel2 = [];
            $deptosNivel3 = [];
            foreach ($xmlResult->registros->departamentos->departamento as $departamento) {
                $nivel = (int)$departamento->nivel->__toString();
                if ($nivel === 1) {
                    $deptosNivel1[] = [
                        'id' => (int)$departamento->idDepartamento->__toString(),
                        'nome' => $departamento->nome->__toString()
                    ];
                } elseif ($nivel === 2) {
                    $deptosNivel2[] = [
                        'id' => (int)$departamento->idDepartamento->__toString(),
                        'idNivel1' => (int)$departamento->idDepartamentoNivel1->__toString(),
                        'nome' => $departamento->nome->__toString()
                    ];
                } elseif ($nivel === 3) {
                    $deptosNivel3[] = [
                        'id' => (int)$departamento->idDepartamento->__toString(),
                        'idNivel1' => (int)$departamento->idDepartamentoNivel1->__toString(),
                        'idNivel2' => (int)$departamento->idDepartamentoNivel2->__toString(),
                        'nome' => $departamento->nome->__toString()
                    ];
                }
            }
            foreach ($deptosNivel1 as $deptoNivel1) {
                $this->deptosNaWebStorm[$deptoNivel1['id']] = [
                    'nome' => $deptoNivel1['nome'],
                    'grupos' => []
                ];
                foreach ($deptosNivel2 as $deptoNivel2) {
                    if ($deptoNivel2['idNivel1'] === $deptoNivel1['id']) {
                        $this->deptosNaWebStorm[$deptoNivel1['id']]['grupos'][$deptoNivel2['id']] = [
                            'nome' => $deptoNivel2['nome'],
                            'subgrupos' => []
                        ];

                        foreach ($deptosNivel3 as $deptoNivel3) {
                            if ($deptoNivel3['idNivel2'] === $deptoNivel2['id']) {
                                $this->deptosNaWebStorm[$deptoNivel1['id']]['grupos'][$deptoNivel2['id']]['subgrupos'][$deptoNivel3['id']] = [
                                    'nome' => $deptoNivel3['nome']
                                ];
                            }
                        }

                    }

                }
            }
        }
        return $this->deptosNaWebStorm;
    }

    /**
     * @param Depto $depto
     * @return int
     * @throws ViewException
     */
    public function integraDepto(Depto $depto): int
    {
        $deptosNaWebStorm = $this->selectDepartamentosNaWebStorm();
        $idDeptoWebStorm = null;
        foreach ($deptosNaWebStorm as $id => $deptoNaWebStorm) {
            if ($deptoNaWebStorm['nome'] === $depto->nome) {
                $idDeptoWebStorm = $id;
                break;
            }
        }
        if (!$idDeptoWebStorm) {
            $idDeptoWebStorm = $this->integraDeptoGrupoSubgrupo($depto->nome, 1);
        }
        if (!isset($depto->jsonData['ecommerce_id']) || $depto->jsonData['ecommerce_id'] !== $idDeptoWebStorm) {
            $depto->jsonData = [
                'ecommerce_id' => $idDeptoWebStorm,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
            $this->deptoEntityHandler->save($depto);
        }

        return $idDeptoWebStorm;
    }

    /**
     * @param Grupo $grupo
     * @return int
     * @throws ViewException
     */
    public function integraGrupo(Grupo $grupo): int
    {
        /** @var GrupoRepository $repoGrupo */
        $repoGrupo = $this->getDoctrine()->getRepository(Grupo::class);
        $grupo = $repoGrupo->find($grupo->getId());
        $idDeptoWebStorm = $grupo->depto->jsonData['ecommerce_id'];

        $deptosNaWebStorm = $this->selectDepartamentosNaWebStorm();
        if (!isset($deptosNaWebStorm[$idDeptoWebStorm])) {
            throw new \RuntimeException('idDeptoWebStorm N/D: ' . $idDeptoWebStorm);
        }
        $gruposNaWebStorm = $deptosNaWebStorm[$idDeptoWebStorm]['grupos'];
        $idGrupoWebStorm = null;
        foreach ($gruposNaWebStorm as $id => $grupoNaWebStorm) {
            if ($grupoNaWebStorm['nome'] === $grupo->nome) {
                $idGrupoWebStorm = $id;
                break;
            }
        }

        if (!$idGrupoWebStorm) {
            $idGrupoWebStorm = $this->integraDeptoGrupoSubgrupo($grupo->nome, 2, $idDeptoWebStorm);
        }

        if (!isset($grupo->jsonData['ecommerce_id']) || $grupo->jsonData['ecommerce_id'] !== $idGrupoWebStorm) {
            $grupo->jsonData = [
                'ecommerce_id' => $idGrupoWebStorm,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];

            $this->grupoEntityHandler->save($grupo);
        }
        return $idGrupoWebStorm;
    }

    /**
     * @param Subgrupo $subgrupo
     * @return int
     * @throws ViewException
     */
    public function integraSubgrupo(Subgrupo $subgrupo): int
    {
        /** @var SubgrupoRepository $repoSubgrupo */
        $repoSubgrupo = $this->getDoctrine()->getRepository(Subgrupo::class);
        $subgrupo = $repoSubgrupo->find($subgrupo->getId());

        $idGrupoWebStorm = $subgrupo->grupo->jsonData['ecommerce_id'];
        $idDeptoWebStorm = $subgrupo->grupo->depto->jsonData['ecommerce_id'];

        $deptosNaWebStorm = $this->selectDepartamentosNaWebStorm();
        if (!isset($deptosNaWebStorm[$idDeptoWebStorm]['grupos'][$idGrupoWebStorm])) {
            throw new \RuntimeException('idGrupoWebStorm N/D: ' . $idGrupoWebStorm);
        }
        $subgruposNaWebStorm = $deptosNaWebStorm[$idDeptoWebStorm]['grupos'][$idGrupoWebStorm]['subgrupos'];
        $idSubgrupoWebStorm = null;
        foreach ($subgruposNaWebStorm as $id => $subgrupoNaWebStorm) {
            if ($subgrupoNaWebStorm['nome'] === $subgrupo->nome) {
                $idSubgrupoWebStorm = $id;
                break;
            }
        }

        if (!$idSubgrupoWebStorm) {
            $idSubgrupoWebStorm = $this->integraDeptoGrupoSubgrupo($subgrupo->nome, 3, $idDeptoWebStorm, $idGrupoWebStorm);
        }

        if (!isset($subgrupo->jsonData['ecommerce_id']) || $subgrupo->jsonData['ecommerce_id'] !== $idSubgrupoWebStorm) {
            $subgrupo->jsonData = [
                'ecommerce_id' => $idSubgrupoWebStorm,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
            $this->subgrupoEntityHandler->save($subgrupo);
        }
        return $idSubgrupoWebStorm;
    }

    /**
     * Integra um Depto, Grupo ou Subgrupo.
     *
     * @param string $descricao
     * @param int $nivel
     * @param int|null $idNivelPai1
     * @param int|null $idNivelPai2
     * @param int|null $ecommerce_id
     * @return int
     */
    private function integraDeptoGrupoSubgrupo(string $descricao, int $nivel, ?int $idNivelPai1 = null, ?int $idNivelPai2 = null, ?int $ecommerce_id = null)
    {
        $client = $this->getNusoapClientImportacaoInstance();

        $pais = '';
        if ($nivel === 2) {
            $pais = '<idDepartamentoNivel1>' . $idNivelPai1 . '</idDepartamentoNivel1>';
        } elseif ($nivel === 3) {
            $pais = '<idDepartamentoNivel1>' . $idNivelPai1 . '</idDepartamentoNivel1>';
            $pais .= '<idDepartamentoNivel2>' . $idNivelPai2 . '</idDepartamentoNivel2>';
        }

        $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>' . ($ecommerce_id ? 'update' : 'insert') . '</acao>
               <modulo>departamento</modulo>
               <marca pk="idDepartamento">
                  <idDepartamento>' . $ecommerce_id . '</idDepartamento>
                  <nome>' . $descricao . '</nome>
                  <nivel>' . $nivel . '</nivel>' . $pais . '
               </marca>
            </ws_integracao>]]>';

        $arResultado = $client->call('departamento' . ($ecommerce_id ? 'Update' : 'Add'), [
            'xml' => utf8_decode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }
        $arResultado = utf8_encode($arResultado);
        $xmlResult = simplexml_load_string($arResultado);

        $this->deptosNaWebStorm = null; // para forçar rechecagem


        return (int)$xmlResult->idDepartamento->__toString();
    }


    /**
     * @param Produto $produto
     * @return void
     * @throws ViewException
     */
    public function integraProduto(Produto $produto): void
    {
        /** @var AppConfig $appConfig */
        $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
        if (!$appConfig) {
            throw new \LogicException('est_produto_json_metadata N / D');
        }

        $jsonCampos = json_decode($appConfig->getValor(), true)['campos'];

        // Verifica se o depto, grupo e subgrupo já estão integrados
        $idDepto = $produto->depto->jsonData['ecommerce_id'] ?? $this->integraDepto($produto->depto);
        $idGrupo = $produto->grupo->jsonData['ecommerce_id'] ?? $this->integraGrupo($produto->grupo);
        $idSubgrupo = $produto->subgrupo->jsonData['ecommerce_id'] ?? $this->integraSubgrupo($produto->subgrupo);

        $idMarca = null;
        if ($produto->jsonData['marca'] ?? false) {
            $idMarca = $this->integraMarca($produto->jsonData['marca']);
        }

        $dimensoes = [];
        if (isset($produto->jsonData['dimensoes'])) {
            $dimensoes = explode('|', $produto->jsonData['dimensoes']);
        }
        $altura = $dimensoes[0] ?? '';
        $largura = $dimensoes[1] ?? '';
        $comprimento = $dimensoes[2] ?? '';

        $produtoEcommerceId = $produto->jsonData['ecommerce_id'] ?? null;

        $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>' . ($produtoEcommerceId ? 'update' : 'insert') . '</acao>
               <modulo>produto</modulo>
               <produto pk="idProduto">' .
            ($produtoEcommerceId ? '<idProduto>' . $produtoEcommerceId . '</idProduto>' : '');
        $xml .= $idMarca ? '<idMarca>' . $idMarca . '</idMarca>' : '';
        $xml .=
            '<departamento pk="idDepartamento"><idDepartamento>' . $idDepto . '</idDepartamento></departamento>' .
            '<departamento pk="idDepartamento"><idDepartamento>' . $idGrupo . '</idDepartamento></departamento>' .
            '<departamento pk="idDepartamento"><idDepartamento>' . $idSubgrupo . '</idDepartamento></departamento>' .
            '<situacao>1</situacao>' .
            '<prazoXD>0</prazoXD>' .
            '<conjunto />' .
            '<nome>' . $produto->jsonData['titulo'] . '</nome>' .
            '<descricao-caracteristicas>' . htmlspecialchars($produto->jsonData['caracteristicas'] ?? '') . '</descricao-caracteristicas>' .
            '<descricao-itens-inclusos>' . htmlspecialchars($produto->jsonData['itens_inclusos'] ?? '') . '</descricao-itens-inclusos>' .
            '<descricao-compativel-com>' . htmlspecialchars($produto->jsonData['compativel_com'] ?? '') . '</descricao-compativel-com>' .
            '<descricao-especificacoes-tecnicas>' . htmlspecialchars($produto->jsonData['especif_tec'] ?? '') . '</descricao-especificacoes-tecnicas>';


        foreach ($produto->jsonData as $campo => $valor) {
            if (isset($jsonCampos[$campo]['info_integr_ecommerce']['tipo_campo_ecommerce']) && $jsonCampos[$campo]['info_integr_ecommerce']['tipo_campo_ecommerce'] === 'caracteristica') {

                if ($jsonCampos[$campo]['info_integr_ecommerce']['ecommerce_id'] ?: null) {
                    $ecommerceId_tipoCaracteristica = (int)$jsonCampos[$campo]['info_integr_ecommerce']['ecommerce_id'];
                } else {
                    $ecommerceId_tipoCaracteristica = (int)$this->integraTipoCaracteristica($campo, $jsonCampos[$campo]['label']);
                }

                if ($jsonCampos[$campo]['tipo'] === 'tags') {
                    $valoresTags = explode(',', $valor);
                    foreach ($valoresTags as $valorTag) {
                        $ecommerceId_caracteristica = $this->integraCaracteristica($campo, $ecommerceId_tipoCaracteristica, $valorTag);
                        $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                    }
                } else {
                    $ecommerceId_caracteristica = $this->integraCaracteristica($campo, $ecommerceId_tipoCaracteristica, $valor);
                    $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                }
            }
        }

        foreach ($produto->imagens as $imagem) {
            $url = $_SERVER['CROSIERAPP_URL'] . $this->uploaderHelper->asset($imagem, 'imageFile');
            $xml .= '<imagens>
				<url>' . $url . '</url>
				<prioridade>' . ($imagem->getOrdem() - 1) . '</prioridade>
			</imagens>';
        }


        $xml .=
            '<itensVenda>
				<idItemVenda></idItemVenda>
				<codigo>' . $produto->jsonData['referencia'] . '</codigo>
				<preco>' . ($produto->jsonData['preco_site'] ?? $produto->jsonData['preco_tabela'] ?? 0.0) . '</preco>
				<estoque>999999</estoque>
				<estoqueMin>0</estoqueMin>
				<situacao>1</situacao>
				<peso>' . ($produto->jsonData['peso'] ?? '') . '</peso>
				<ean>' . ($produto->jsonData['ean'] ?? '') . '</ean>
				<altura>' . $altura . '</altura>
				<largura>' . $largura . '</largura>
				<comprimento>' . $comprimento . '</comprimento>
            </itensVenda></produto>' .
            '</ws_integracao>]]>';

        echo "<textarea>";
        echo $xml;
        echo "</textarea>";

        $client = $this->getNusoapClientImportacaoInstance();

        $arResultado = $client->call('produto' . ($produtoEcommerceId ? 'Update' : 'Add'), [
            'xml' => utf8_decode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $arResultado = utf8_encode($arResultado);
        $arResultado = str_replace('&nbsp;', ' ', $arResultado);
        $xmlResult = simplexml_load_string($arResultado);

        if ($xmlResult->erros->erro ?? false) {
            throw new \RuntimeException($xmlResult->erros->erro->__toString());
        }

        if (!$produtoEcommerceId) {
            $produto->jsonData['ecommerce_id'] = (int)$xmlResult->produto->produto->idProduto->__toString();
            $this->produtoEntityHandler->save($produto);
        }

        $produto->jsonData['ecommerce_integrado_em'] = (new \DateTime())->format('d/m/Y H:i:s');

    }


    /**
     * @return \nusoap_client
     */
    private function getNusoapClientExportacaoInstance(): \nusoap_client
    {
        if (!isset($this->nusoapClientExportacao)) {

            $endpoint = $this->getDoctrine()->getRepository(AppConfig::class)
                ->findValorByChaveAndAppUUID('ecomm_info_integra_WEBSTORM_endpoint_export', $_SERVER['CROSIERAPP_UUID']);
            if (!$endpoint) {
                throw new \RuntimeException('endpoint não informado');
            }
            $client = new \nusoap_client($endpoint, 'wsdl', false, false, false, false, 300, 300);
            // $client->setEndpoint($endpoint);
            // $client->soap_defencoding = 'UTF-8';
            // $client->decode_utf8 = false;
            $client->setCurlOption(CURLOPT_SSLVERSION, 4);
            $client->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
            $client->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);

            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $this->nusoapClientExportacao = $client;
        }
        return $this->nusoapClientExportacao;
    }

    /**
     * @return \nusoap_client
     */
    private function getNusoapClientImportacaoInstance(): \nusoap_client
    {
        if (!isset($this->nusoapClientImportacao)) {

            $endpoint = $this->getDoctrine()->getRepository(AppConfig::class)
                ->findValorByChaveAndAppUUID('ecomm_info_integra_WEBSTORM_endpoint_import', $_SERVER['CROSIERAPP_UUID']);
            if (!$endpoint) {
                throw new \RuntimeException('endpoint não informado');
            }
            $client = new \nusoap_client($endpoint, 'wsdl', false, false, false, false, 300, 300);
            // $client->setEndpoint('https://rodoponta.webstorm.com.br/webservice/serverImportacao');
            $client->soap_defencoding = 'iso-8859-1';
            $client->decode_utf8 = false;
            $client->setCurlOption(CURLOPT_SSLVERSION, 4);
            $client->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
            $client->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);

            if ($client->getError()) {
                throw new \RuntimeException($client->getError());
            }

            $this->nusoapClientImportacao = $client;
        }
        return $this->nusoapClientImportacao;
    }


}