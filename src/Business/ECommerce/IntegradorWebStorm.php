<?php


namespace App\Business\ECommerce;

use App\Messenger\Message\IntegrarProdutoEcommerceMessage;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ImageUtils\ImageUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\WebUtils\WebUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Depto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Grupo;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Subgrupo;
use CrosierSource\CrosierLibRadxBundle\Entity\RH\Colaborador;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\PlanoPagto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\VendaItem;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\CRM\ClienteEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\DeptoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\GrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\SubgrupoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\DeptoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\GrupoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\ProdutoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Estoque\SubgrupoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\RH\ColaboradorRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\PlanoPagtoRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Regras de negócio para a integração com a WebStorm.
 *
 * Class IntegradorWebStorm
 * @package App\Business\ECommerce
 * @author Carlos Eduardo Pauluk
 */
class IntegradorWebStorm implements IntegradorBusiness
{

    private ?string $chave = null;

    private \nusoap_client $nusoapClientExportacao;

    private \nusoap_client $nusoapClientImportacao;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private Security $security;

    private AppConfigRepository $repoAppConfig;

    private DeptoEntityHandler $deptoEntityHandler;

    private GrupoEntityHandler $grupoEntityHandler;

    private SubgrupoEntityHandler $subgrupoEntityHandler;

    private ProdutoEntityHandler $produtoEntityHandler;

    private VendaEntityHandler $vendaEntityHandler;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private ClienteEntityHandler $clienteEntityHandler;

    private UploaderHelper $uploaderHelper;

    private ?array $tiposCaracteristicasNaWebStorm = null;

    private ?array $deptosNaWebStorm = null;

    private ?array $marcasNaWebStorm = null;

    private ParameterBagInterface $params;

    private MessageBusInterface $bus;

    private SyslogBusiness $syslog;

    public function __construct(
        AppConfigEntityHandler $appConfigEntityHandler,
        Security $security,
        DeptoEntityHandler $deptoEntityHandler,
        GrupoEntityHandler $grupoEntityHandler,
        SubgrupoEntityHandler $subgrupoEntityHandler,
        ProdutoEntityHandler $produtoEntityHandler,
        UploaderHelper $uploaderHelper,
        ParameterBagInterface $params,
        ClienteEntityHandler $clienteEntityHandler,
        VendaEntityHandler $vendaEntityHandler,
        VendaItemEntityHandler $vendaItemEntityHandler,
        MessageBusInterface $bus,
        SyslogBusiness $syslog,
        AppConfigRepository $repoAppConfig)
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->security = $security;
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->uploaderHelper = $uploaderHelper;
        $this->params = $params;
        $this->clienteEntityHandler = $clienteEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->bus = $bus;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->repoAppConfig = $repoAppConfig;
    }

    /**
     * @return string
     */
    public function getChave(): string
    {
        if (!$this->chave) {
            try {
                /** @var AppConfigRepository $repoAppConfig */
                $this->repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
                /** @var AppConfig $appConfigChave */
                $appConfigChave = $this->repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_WEBSTORM_chave'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
                if ($appConfigChave) {
                    $this->chave = $appConfigChave->getValor();
                }
            } catch (\Exception $e) {
                throw new \RuntimeException('Erro ao instanciar IntegradorWebStorm (chave ecomm_info_integra_WEBSTORM_chave ?)');
            }
        }
        return $this->chave;
    }


    /**
     * Obtém as marcas cadastradas na WebStorm
     * @return array
     * @throws ViewException
     */
    public function selectMarcasNaWebStorm(): array
    {
        if (!$this->marcasNaWebStorm) {
            $this->syslog->info('selectMarcasNaWebStorm');
            $client = $this->getNusoapClientExportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
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
                $err = 'selectMarcasNaWebStorm - faultcode: ' . (string)$client->faultcode;
                $this->syslog->err($err);
                throw new ViewException($err);
            }
            // else
            if ($client->getError()) {
                $err = 'selectMarcasNaWebStorm - error: ' . $client->getError();
                $this->syslog->err($err);
                throw new ViewException($err);
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                $err = $xmlResult->erros->erro->__toString();
                $this->syslog->err('selectMarcasNaWebStorm - erros: ' . $xmlResult->erros->erro->__toString());
                throw new \RuntimeException($err);
            }

            $this->marcasNaWebStorm = [];
            foreach ($xmlResult->registros->marcas->marca as $marca) {
                $this->marcasNaWebStorm[(int)$marca->idMarca->__toString()] = [
                    'nome' => $marca->nome->__toString(),
                ];
            }
            $this->syslog->info('selectMarcasNaWebStorm - OK: ' . count($this->marcasNaWebStorm) . ' marca(s)');
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
        $this->syslog->info('integraMarca: ini', 'marca = ' . $marca);
        $marcasNaWebStorm = $this->selectMarcasNaWebStorm();

        $idMarcaNaWebStorm = null;

        foreach ($marcasNaWebStorm as $id => $marcaNaWebStorm) {
            if (mb_strtolower($marcaNaWebStorm['nome']) === mb_strtolower($marca)) {
                $idMarcaNaWebStorm = $id;
                break;
            }
        }

        if (!$idMarcaNaWebStorm) {
            $this->syslog->info('integraMarca: não existe, enviando...', 'marca = ' . $marca);

            $client = $this->getNusoapClientImportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->getChave() . '</chave>
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
                $this->syslog->err('integraMarca: faultcode ' . (string)$client->faultcode, 'marca = ' . $marca);
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                $this->syslog->err('integraMarca: error ' . $client->getError(), 'marca = ' . $marca);
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            $idMarcaNaWebStorm = (int)$xmlResult->idMarca->__toString();

            $this->syslog->info('integraMarca: OK ', 'marca = ' . $marca);
        }

        return $idMarcaNaWebStorm;
    }

    /**
     * Obtém os tipos de características cadastrados na WebStorm
     * @return array
     * @throws ViewException
     */
    public function selectTiposCaracteristicasNaWebStorm(): array
    {
        if (!$this->tiposCaracteristicasNaWebStorm) {
            $client = $this->getNusoapClientExportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
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
                throw new ViewException('selectTiposCaracteristicasNaWebStorm (registrosSelect >> tipoCaracteristica): faultcode ' . (string)$client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new ViewException('selectTiposCaracteristicasNaWebStorm (registrosSelect >> tipoCaracteristica): error ' . $client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                throw new ViewException('selectTiposCaracteristicasNaWebStorm (registrosSelect >> tipoCaracteristica): erros ' . $xmlResult->erros->erro->__toString());
            }

            $this->tiposCaracteristicasNaWebStorm = [];
            foreach ($xmlResult->registros->tipoCaracteristicas->tipoCaracteristica as $tipoCaracteristica) {
                $this->tiposCaracteristicasNaWebStorm[(int)$tipoCaracteristica->idTipoCaracteristica->__toString()] = [
                    'nome' => $tipoCaracteristica->nome->__toString(),
                ];
            }

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
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
                throw new ViewException('selectTiposCaracteristicasNaWebStorm (registrosSelect >> caracteristica): faultcode ' . (string)$client->faultcode);
            }
            // else
            if ($client->getError()) {
                throw new ViewException('selectTiposCaracteristicasNaWebStorm (registrosSelect >> caracteristica): error ' . $client->getError());
            }

            $xmlResult = simplexml_load_string(utf8_encode($arResultado));

            if ($xmlResult->erros ?? false) {
                throw new ViewException('selectTiposCaracteristicasNaWebStorm (registrosSelect >> caracteristica): erros ' . $xmlResult->erros->erro->__toString());
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
        $syslog_obs = 'campo = ' . $campo . '; tipoCaracteristica = ' . $tipoCaracteristica;
        $this->syslog->info('integraTipoCaracteristica: ini', $syslog_obs);
        $tiposCaracteristicasNaWebStorm = $this->selectTiposCaracteristicasNaWebStorm();

        $idTipoCaracteristicaNaWebStorm = null;

        foreach ($tiposCaracteristicasNaWebStorm as $id => $tipoCaracteristicaNaWebStorm) {
            if (mb_strtolower($tipoCaracteristicaNaWebStorm['nome']) === mb_strtolower($tipoCaracteristica)) {
                $idTipoCaracteristicaNaWebStorm = $id;
                break;
            }
        }

        if (!$idTipoCaracteristicaNaWebStorm) {
            $this->syslog->info('integraTipoCaracteristica: não existe, enviando...', $syslog_obs);

            $client = $this->getNusoapClientImportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->getChave() . '</chave>
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
                $this->syslog->err('integraTipoCaracteristica: faultcode ' . (string)$client->faultcode, $syslog_obs);
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                $this->syslog->err('integraTipoCaracteristica: error ' . $client->getError(), $syslog_obs);
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                $this->syslog->err('integraTipoCaracteristica: erros: ' . $xmlResult->erros->__toString(), $syslog_obs);
            }

            $idTipoCaracteristicaNaWebStorm = (int)$xmlResult->idTipoCaracteristica->__toString();

            $this->syslog->info('integraTipoCaracteristica: enviado (idTipoCaracteristicaNaWebStorm: ' . $idTipoCaracteristicaNaWebStorm . ')', $syslog_obs);

            $this->tiposCaracteristicasNaWebStorm = null; // para forçar a rebusca
        }

        try {
            $this->syslog->info('integraTipoCaracteristica: salvando na cfg_app_config [\'campos\'][' . $campo . '][\'info_integr_ecommerce\'][\'ecommerce_id\']', $syslog_obs);
            /** @var AppConfig $appConfig */
            $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
            $jsonMetadata = json_decode($appConfig->getValor(), true);
            $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['ecommerce_id'] = $idTipoCaracteristicaNaWebStorm;
            $appConfig->setValor(json_encode($jsonMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->appConfigEntityHandler->save($appConfig);
            $this->syslog->info('integraTipoCaracteristica: OK', $syslog_obs);
        } catch (ViewException $e) {
            $this->syslog->err('integraTipoCaracteristica: salvando na cfg_app_config - ERRO: ' . $e->getMessage(), $syslog_obs);
        }


        return $idTipoCaracteristicaNaWebStorm;
    }

    /**
     * @param int $ecommerceId_tipoCaracteristica
     * @param string $caracteristica
     * @return int
     * @throws ViewException
     */
    private function integraCaracteristica(int $ecommerceId_tipoCaracteristica, string $caracteristica): int
    {
        $syslog_obs = 'ecommerceId_tipoCaracteristica = ' . $ecommerceId_tipoCaracteristica . '; caracteristica = ' . $caracteristica;
        $this->syslog->info('integraCaracteristica: ini', $syslog_obs);
        $tiposCaracteristicasNaWebStorm = $this->selectTiposCaracteristicasNaWebStorm();

        $idCaracteristicaNaWebStorm = null;

        if ($tiposCaracteristicasNaWebStorm[$ecommerceId_tipoCaracteristica]['caracteristicas'] ?? null) {
            foreach ($tiposCaracteristicasNaWebStorm[$ecommerceId_tipoCaracteristica]['caracteristicas'] as $id => $caracteristicaNaWebStorm) {
                // Compara ignorando maiúsculas e minúsculas
                if (mb_strtolower($caracteristicaNaWebStorm['nome']) === mb_strtolower($caracteristica)) {
                    $idCaracteristicaNaWebStorm = $id;
                    break;
                }
            }
        }

        if (!$idCaracteristicaNaWebStorm) {
            $this->syslog->info('integraCaracteristica: não existe, enviando...', $syslog_obs);

            $client = $this->getNusoapClientImportacaoInstance();

            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->getChave() . '</chave>
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
                $this->syslog->err('integraCaracteristica: faultcode ' . (string)$client->faultcode, $syslog_obs);
                throw new \RuntimeException($client->faultcode);
            }
            // else
            if ($client->getError()) {
                $this->syslog->err('integraCaracteristica: error ' . $client->getError(), $syslog_obs);
                throw new \RuntimeException($client->getError());
            }

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros ?? false) {
                $this->syslog->err('integraCaracteristica: erros: ' . $xmlResult->erros->__toString(), $syslog_obs);
            }

            $idCaracteristicaNaWebStorm = (int)$xmlResult->caracteristicas->caracteristica->idCaracteristica->__toString();

            $this->syslog->info('integraCaracteristica: enviado (idCaracteristicaNaWebStorm: ' . $idCaracteristicaNaWebStorm . ')', $syslog_obs);

            $this->tiposCaracteristicasNaWebStorm = null; // para forçar a rebusca
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
        $repoDepto = $this->deptoEntityHandler->getDoctrine()->getRepository(Depto::class);
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
        $syslog_obs = 'depto = ' . $depto->nome . ' (' . $depto->getId() . ')';
        $this->syslog->info('integraDepto - ini', $syslog_obs);
        $deptosNaWebStorm = $this->selectDepartamentosNaWebStorm();
        $idDeptoWebStorm = null;
        foreach ($deptosNaWebStorm as $id => $deptoNaWebStorm) {
            if ($deptoNaWebStorm['nome'] === $depto->nome) {
                $idDeptoWebStorm = $id;
                break;
            }
        }
        if (!$idDeptoWebStorm) {
            $this->syslog->info('integraDepto - não existe, enviando...', $syslog_obs);
            $idDeptoWebStorm = $this->integraDeptoGrupoSubgrupo($depto->nome, 1);
            $this->syslog->info('integraDepto - integrado', $syslog_obs);
        }
        if (!isset($depto->jsonData['ecommerce_id']) || $depto->jsonData['ecommerce_id'] !== $idDeptoWebStorm) {
            $this->syslog->info('integraDepto - salvando json_data', $syslog_obs);
            $depto->jsonData = [
                'ecommerce_id' => $idDeptoWebStorm,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d'
            ];
            $this->deptoEntityHandler->save($depto);
            $this->syslog->info('integraDepto - salvando json_data: OK', $syslog_obs);
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
        $syslog_obs = 'grupo = ' . $grupo->nome . ' (' . $grupo->getId() . ')';
        $this->syslog->info('integraGrupo - ini', $syslog_obs);

        /** @var GrupoRepository $repoGrupo */
        $repoGrupo = $this->grupoEntityHandler->getDoctrine()->getRepository(Grupo::class);
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
            $this->syslog->info('integraGrupo - não existe, enviando...', $syslog_obs);
            $idGrupoWebStorm = $this->integraDeptoGrupoSubgrupo($grupo->nome, 2, $idDeptoWebStorm);
            $this->syslog->info('integraGrupo - integrado', $syslog_obs);
        }

        if (!isset($grupo->jsonData['ecommerce_id']) || $grupo->jsonData['ecommerce_id'] !== $idGrupoWebStorm) {
            $this->syslog->info('integraGrupo - salvando json_data', $syslog_obs);
            $grupo->jsonData = [
                'ecommerce_id' => $idGrupoWebStorm,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d'
            ];

            $this->grupoEntityHandler->save($grupo);
            $this->syslog->info('integraGrupo - salvando json_data: OK', $syslog_obs);
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
        $syslog_obs = 'subgrupo = ' . $subgrupo->nome . ' (' . $subgrupo->getId() . ')';
        $this->syslog->info('integraSubgrupo - ini', $syslog_obs);

        /** @var SubgrupoRepository $repoSubgrupo */
        $repoSubgrupo = $this->subgrupoEntityHandler->getDoctrine()->getRepository(Subgrupo::class);
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
            $this->syslog->info('integraSubgrupo - não existe, enviando...', $syslog_obs);
            $idSubgrupoWebStorm = $this->integraDeptoGrupoSubgrupo($subgrupo->nome, 3, $idDeptoWebStorm, $idGrupoWebStorm);
            $this->syslog->info('integraSubgrupo - integrado', $syslog_obs);
        }

        if (!isset($subgrupo->jsonData['ecommerce_id']) || $subgrupo->jsonData['ecommerce_id'] !== $idSubgrupoWebStorm) {
            $this->syslog->info('integraSubgrupo - salvando json_data', $syslog_obs);
            $subgrupo->jsonData = [
                'ecommerce_id' => $idSubgrupoWebStorm,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d'
            ];
            $this->subgrupoEntityHandler->save($subgrupo);
            $this->syslog->info('integraSubgrupo - salvando json_data: OK', $syslog_obs);
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
        $syslog_obs =
            'descricao = ' . $descricao . ', ' .
            'nivel = ' . $nivel . ', ' .
            'idNivelPai1 = ' . $idNivelPai1 . ', ' .
            'idNivelPai2 = ' . $idNivelPai2 . ', ' .
            'ecommerce_id = ' . $ecommerce_id;
        $this->syslog->info('integraDeptoGrupoSubgrupo - ini', $syslog_obs);
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
               <chave>' . $this->getChave() . '</chave>
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
            $err = 'integraDeptoGrupoSubgrupo - faultcode: ' . (string)$client->faultcode;
            $this->syslog->err($err, $syslog_obs);
            throw new \RuntimeException($err);
        }
        // else
        if ($client->getError()) {
            $err = 'integraDeptoGrupoSubgrupo - error: ' . $client->getError();
            $this->syslog->err($err, $syslog_obs);
            throw new \RuntimeException($err);
        }
        $arResultado = utf8_encode($arResultado);
        $xmlResult = simplexml_load_string($arResultado);

        $this->deptosNaWebStorm = null; // para forçar rechecagem

        $idDepartamento = (int)$xmlResult->idDepartamento->__toString();
        $this->syslog->info('integraDeptoGrupoSubgrupo - OK (idDepartamento = ' . $idDepartamento . ')', $syslog_obs);
        return $idDepartamento;
    }


    /**
     * @param Produto $produto
     * @param bool $integrarImagens
     * @return void
     * @throws ViewException
     */
    public function integraProduto(Produto $produto, ?bool $integrarImagens = true): void
    {
        $start = microtime(true);
        $syslog_obs = 'produto = ' . $produto->getId() . '; integrarImagens = ' . $integrarImagens;
        $this->syslog->info('integraProduto - ini', $syslog_obs);
        /** @var AppConfig $appConfig */
        $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
        if (!$appConfig) {
            $err = 'est_produto_json_metadata N/D';
            $this->syslog->err($err, $syslog_obs);
            throw new \RuntimeException($err);
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

        $produtoEcommerceId = null;
        $produtoItemVendaId = null;
        if (isset($produto->jsonData['ecommerce_id']) && $produto->jsonData['ecommerce_id'] > 0) {
            $produtoEcommerceId = $produto->jsonData['ecommerce_id'];
            $produtoItemVendaId = $produto->jsonData['ecommerce_item_venda_id'] ?? null;
        }


        if (!$integrarImagens && !$produtoEcommerceId) {
            $err = 'Produto ainda não integrado. É necessário integrar as imagens!';
            $this->syslog->err($err, $syslog_obs);
            throw new ViewException($err);
        }

        $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->getChave() . '</chave>
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
            '<descricao>' . $produto->jsonData['descricao_produto'] . '</descricao>' .
            '<referencia>' . ($produto->jsonData['referencia'] ?? '') . '</referencia>' .
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
                        $ecommerceId_caracteristica = $this->integraCaracteristica($ecommerceId_tipoCaracteristica, $valorTag);
                        $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                    }
                } else {
                    $ecommerceId_caracteristica = $this->integraCaracteristica($ecommerceId_tipoCaracteristica, $valor);
                    $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                }
            }
        }

        if ($integrarImagens) {
            foreach ($produto->imagens as $imagem) {
                $url = $_SERVER['CROSIERAPP_URL'] . $this->uploaderHelper->asset($imagem, 'imageFile');
                // verifica se existe a imagem "_1080.ext"
                $pathinfo = pathinfo($url);
                $parsedUrl = parse_url($url);
                $url1080 = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_1080.' . $pathinfo['extension'];
                try {
                    if (!WebUtils::urlNot404($url1080)) {
                        $imgDims = getimagesize($url);
                        if ($imgDims[0] > 1500 || $imgDims[1] > 1500) {
                            $imgUtils = new ImageUtils();
                            $imgUtils->load($url);
                            if ($imgDims[0] >= $imgDims[1]) {
                                // largura maior que altura
                                $imgUtils->resizeToWidth(1080);
                            } else {
                                $imgUtils->resizeToHeight(1080);
                            }
                            // '%kernel.project_dir%/public/images/produtos'
                            $file1080 = $this->params->get('kernel.project_dir') . '/public' .
                                str_replace($pathinfo['basename'], '', $parsedUrl['path']) .
                                $pathinfo['filename'] . '_1080.' . $pathinfo['extension'];
                            $imgUtils->save($file1080);
                        } else {
                            $url1080 = $url;
                        }
                    }
                } catch (\Exception $e) {
                    $err = 'Erro ao processar imagens: ' . $e->getMessage();
                    $this->syslog->err($err);
                    throw new \RuntimeException($err);
                }

                $xml .= '<imagens>
				<url>' . $url1080 . '</url>
				<prioridade>' . ($imagem->getOrdem() - 1) . '</prioridade>
			</imagens>';
            }
        }


        $xml .=
            '<itensVenda>
				<idItemVenda>' . $produtoItemVendaId . '</idItemVenda>
				<codigo>' . $produto->getId() . '</codigo>
				<preco>' . ($produto->jsonData['preco_site'] ?? $produto->jsonData['preco_tabela'] ?? 0.0) . '</preco>
				<estoque>' . ($produto->jsonData['qtde_estoque_total'] ?? 999999) . '</estoque>
				<estoqueMin>0</estoqueMin>
				<situacao>1</situacao>
				<peso>' . ($produto->jsonData['peso'] ?? '') . '</peso>
				<ean>' . ($produto->jsonData['ean'] ?? '') . '</ean>
				<altura>' . $altura . '</altura>
				<largura>' . $largura . '</largura>
				<comprimento>' . $comprimento . '</comprimento>
            </itensVenda></produto>' .
            '</ws_integracao>]]>';


        $this->syslog->debug('integraProduto - XML REQUEST - ' . $syslog_obs, $xml);

        $client = $this->getNusoapClientImportacaoInstance();

//        $xml = str_replace('&nbsp;', ' ', $xml);

        $arResultado = $client->call('produto' . ($produtoEcommerceId ? 'Update' : 'Add'), [
            'xml' => utf8_decode($xml)
        ]);

        if ($client->faultcode) {
            $this->syslog->err('integraProduto - faultcode: ' . (string)$client->faultcode, $syslog_obs);
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            $this->syslog->err('integraProduto - faultcode: ' . $client->getError(), $syslog_obs);
            throw new \RuntimeException($client->getError());
        }

        $arResultado = utf8_encode($arResultado);
        $arResultado = str_replace('&nbsp;', ' ', $arResultado);

        $this->syslog->debug('integraProduto - XML RESPONSE - ' . $syslog_obs, $xml);

        $xmlResult = simplexml_load_string($arResultado);

        if ($xmlResult->erros->erro ?? false) {
            $this->syslog->err('integraProduto - erros: ' . $xmlResult->erros->erro->__toString(), $syslog_obs);
            throw new \RuntimeException($xmlResult->erros->erro->__toString());
        }

        // está fazendo UPDATE
        if ($produtoEcommerceId) {
            $produto->jsonData['ecommerce_id'] = (int)$xmlResult->produtos->produto->idProduto->__toString();
            $produto->jsonData['ecommerce_item_venda_id'] = (int)$xmlResult->produtos->produto->itensVenda->itemVenda->idItemVenda->__toString();
        } else {
            $produto->jsonData['ecommerce_id'] = (int)$xmlResult->produto->produto->idProduto->__toString();
            $produto->jsonData['ecommerce_item_venda_id'] = (int)$xmlResult->produto->produto->itensVenda->itemVenda->idItemVenda->__toString();
        }


        $produto->jsonData['ecommerce_dt_integr'] = (new \DateTime())->modify('+1 minutes')->format('Y-m-d H:i:s');
        $produto->jsonData['ecommerce_dt_marcado_integr'] = null;
        $produto->jsonData['ecommerce_integr_por'] = $this->security->getUser() ? $this->security->getUser()->getNome() : 'n/d';

        $this->syslog->info('integraProduto - save', $syslog_obs);
        $this->produtoEntityHandler->save($produto);
        $tt = (int)(microtime(true) - $start);
        $this->syslog->info('integraProduto - OK (em ' . $tt . ' segundos)', $syslog_obs);
    }

    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return int
     * @throws ViewException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int
    {
        // 1 = Novo; 2 = Editado; 3 = Integrado
        // Para pedidos que ainda não foram consultados, o status é 1
        // Após o retorno pelo serviço, o status vai para 3
        // Deve-se, portanto, consultar primeiro com 1 e depois com 3
        $this->obterVendasPorStatus($dtVenda, 1);
        $pedidos = $this->obterVendasPorStatus($dtVenda, 3);
        if (!($pedidos->pedido ?? null)) {
            return 0;
        }
        foreach ($pedidos->pedido as $pedido) {
            $this->integrarVendaFromEcommerce($pedido, $resalvar);
        }
        return count($pedidos);
    }

    /**
     * @param \DateTime $dtVenda
     * @param int $status
     * @return \SimpleXMLElement|void|null
     */
    private function obterVendasPorStatus(\DateTime $dtVenda, int $status)
    {
        $dtIni = (clone $dtVenda)->setTime(0, 0);
        $dtFim = (clone $dtVenda)->setTime(23, 59, 59, 999999);
        $xml = '<![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>    
                    <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
                    <acao>select</acao>
                    <modulo>pedido</modulo>    
                    <filtro>
                        <idPedido></idPedido>
                        <idCliente></idCliente>
                        <cpf_cnpj></cpf_cnpj>
                        <dataInicial>' . $dtIni->format('Y-m-d H:i:s') . '</dataInicial>
                        <dataFinal>' . $dtFim->format('Y-m-d H:i:s') . '</dataFinal>
                        <status></status>
                        <statusNoWebservice>' . $status . '</statusNoWebservice>
                    </filtro>
                    </ws_integracao>]]>';

        $client = $this->getNusoapClientExportacaoInstance();

        $arResultado = $client->call('pedidoSelect', [
            'xml' => utf8_decode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $xmlResult = simplexml_load_string($arResultado);

        if ($xmlResult->erros ?? false) {
            throw new \RuntimeException($xmlResult->erros->erro->__toString());
        }

        return $xmlResult->resultado->filtro ?? null;

    }

    /**
     * @param \SimpleXMLElement $pedido
     * @param bool|null $resalvar
     * @throws ViewException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function integrarVendaFromEcommerce(\SimpleXMLElement $pedido, ?bool $resalvar = false)
    {
        /** @var Connection $conn */
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();

        $venda = $conn->fetchAll('SELECT * FROM ven_venda WHERE json_data->>"$.ecommerce_idPedido" = :ecommerce_idPedido', ['ecommerce_idPedido' => $pedido->idPedido]);
        $venda = $venda[0] ?? null;

        if ($venda) {
            // se já existe, só confere o status
            $vendaJsonData = json_decode($venda['json_data'], true);
            if ($vendaJsonData['ecommerce_status'] != $pedido->status->__toString()) {

                $vendaJsonData['ecommerce_status'] = $pedido->status->__toString();
                $vendaJsonData['ecommerce_status_descricao'] = $pedido->desStatus->__toString();
                $venda_['json_data'] = json_encode($vendaJsonData);
                try {
                    $conn->update('ven_venda', $venda_, ['id' => $venda['id']]);
                } catch (DBALException $e) {
                    throw new ViewException('Erro ao alterar status da venda. (ecommerce_idPedido = ' . $pedido->idPedido->__toString() . ')');
                }
            }

            // Se não estiver pedindo para resalvar as informações novamente (o que irá sobreescrever quaisquer alterações), já retorna...
            if (!$resalvar) {
                return;
            }

            try {
                $conn->delete('ven_venda_item', ['venda_id' => $venda['id']]);
            } catch (\Throwable $e) {
                $erro = 'Erro ao deletar itens da venda (id = "' . $venda['id'] . ')';
                $this->syslog->info($erro);
                throw new \RuntimeException($erro);
            }
            /** @var VendaRepository $repoVenda */
            $repoVenda = $this->vendaEntityHandler->getDoctrine()->getRepository(Venda::class);
            $venda = $repoVenda->find($venda['id']);

        } else {
            $venda = new Venda();
        }

        $venda->dtVenda = DateTimeUtils::parseDateStr($pedido->dataPedido->__toString());

        /** @var PlanoPagtoRepository $repoPlanoPagto */
        $repoPlanoPagto = $this->vendaEntityHandler->getDoctrine()->getRepository(PlanoPagto::class);
        $planoPagtoNaoInformado = $repoPlanoPagto->findOneBy(['codigo' => 999]);
        $venda->planoPagto = $planoPagtoNaoInformado;

        /** @var ColaboradorRepository $repoColaborador */
        $repoColaborador = $this->vendaEntityHandler->getDoctrine()->getRepository(Colaborador::class);
        $vendedorNaoIdentificado = $repoColaborador->findOneBy(['cpf' => '99999999999']);
        $venda->vendedor = $vendedorNaoIdentificado;
        $venda->status = 'PV';

        $cliente = $conn->fetchAll('SELECT id FROM crm_cliente WHERE documento = :documento', ['documento' => $pedido->cliente->cpf_cnpj->__toString()]);
        $cliente = $cliente[0] ?? null;
        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->documento = $pedido->cliente->cpf_cnpj->__toString();
            $cliente->nome = $pedido->cliente->nome->__toString();
            $cliente->jsonData['canal'] = 'ECOMMERCE';
            $cliente->jsonData['dtNascimento'] = $pedido->cliente->dataNascimento_dataCriacao->__toString();
            $cliente->jsonData['email'] = $pedido->cliente->email->__toString();
            $cliente->jsonData['sexo'] = $pedido->cliente->sexo->__toString();
            $cliente = $this->clienteEntityHandler->save($cliente);
        } else {
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->clienteEntityHandler->getDoctrine()->getRepository(Cliente::class);
            $cliente = $repoCliente->find($cliente['id']);
        }
        $venda->cliente = $cliente;


        $venda->jsonData['canal'] = 'ECOMMERCE';
        $venda->jsonData['ecommerce_idPedido'] = $pedido->idPedido->__toString();
        $venda->jsonData['ecommerce_status'] = $pedido->status->__toString();
        $obs = [];

        $obs[] = 'IP: ' . ($pedido->ip ?? null);

        $venda->jsonData['ecommerce_entrega_logradouro'] = ($pedido->entrega->logradouro->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_numero'] = ($pedido->entrega->numero->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_complemento'] = ($pedido->entrega->complemento->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_bairro'] = ($pedido->entrega->bairro->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_cidade'] = ($pedido->entrega->cidade->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_uf'] = ($pedido->entrega->estado->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_cep'] = ($pedido->entrega->cep->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_telefone'] = ($pedido->entrega->telefone->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_frete_calculado'] = ($pedido->entrega->frete->__toString() ?? null);
        $venda->jsonData['ecommerce_entrega_frete_real'] = 0.00;

        $obs[] = 'Pagamento: ' . ($pedido->pagamentos->pagamento->tipoFormaPagamento->__toString() ?? null) . ' - ' . ($pedido->pagamentos->pagamento->nomeFormaPagamento->__toString() ?? null);
        $obs[] = 'Desconto: ' . number_format($pedido->pagamentos->pagamento->desconto->__toString(), 2, ',', '.');
        $obs[] = 'Parcelas: ' . ($pedido->pagamentos->pagamento->parcelas->__toString() ?? null);
        $obs[] = 'Valor Parcela: R$ ' . number_format($pedido->pagamentos->pagamento->valorParcela->__toString(), 2, ',', '.');

        $venda->jsonData['obs'] = implode(PHP_EOL, $obs);

        $venda->jsonData['ecommerce_status'] = $pedido->status->__toString();
        $venda->jsonData['ecommerce_status_descricao'] = $pedido->desStatus->__toString();

        $conn->beginTransaction();
        $this->vendaEntityHandler->save($venda);

        /** @var ProdutoRepository $repoProduto */
        $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);

        $ordem = 1;
        foreach ($pedido->produtos->produto as $produtoWebStorm) {
            /** @var Produto $produto */
            $produto = null;
            try {
                // verifica se já existe uma ven_venda com o json_data.ecommerce_idPedido
                $sProduto = $conn->fetchAssoc('SELECT id FROM est_produto WHERE json_data->>"$.ecommerce_id" = :idProduto', ['idProduto' => $produtoWebStorm->idProduto->__toString()]);
                if (!isset($sProduto['id'])) {
                    throw new \RuntimeException();
                }
                $produto = $repoProduto->find($sProduto['id']);
            } catch (\Throwable $e) {
                throw new ViewException('Erro ao integrar venda. Erro ao pesquisar produto (idProduto = ' . $produtoWebStorm->idProduto->__toString() . ')');
            }
            $vendaItem = new VendaItem();
            $venda->addItem($vendaItem);;
            $vendaItem->descricao = $produto->nome;
            $vendaItem->ordem = $ordem++;
            $vendaItem->precoVenda = $produtoWebStorm->valorUnitario->__toString();
            $vendaItem->qtde = $produtoWebStorm->quantidade->__toString();
            $vendaItem->subtotal = bcmul($vendaItem->precoVenda, $vendaItem->qtde, 2);
            $vendaItem->produto = $produto;

            $vendaItem->jsonData['ecommerce_idItemVenda'] = $produtoWebStorm->idItemVenda->__toString();
            $vendaItem->jsonData['ecommerce_codigo'] = $produtoWebStorm->codigo->__toString();

            $this->vendaItemEntityHandler->save($vendaItem);

        }
        $this->vendaEntityHandler->save($venda);

        $conn->commit();
    }


    /**
     * @param Venda $venda
     * @return \SimpleXMLElement|null
     */
    public function integrarVendaParaECommerce(Venda $venda)
    {
        $xml = '<![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>
		<ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<chave>' . $this->getChave() . '</chave>
			<acao>update</acao>
			<modulo>alterarStatusPedido</modulo>    
			<statusPedido>
				<idPedido>' . $venda->jsonData['ecommerce_idPedido'] . '</idPedido>
				<status>' . $venda->jsonData['ecommerce_status'] . '</status>
			</statusPedido>
		</ws_integracao>]]>';

        $client = $this->getNusoapClientImportacaoInstance();

        $arResultado = $client->call('alterarStatusPedido', [
            'xml' => utf8_decode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $xmlResult = simplexml_load_string($arResultado);

        if ($xmlResult->erros ?? false) {
            throw new \RuntimeException($xmlResult->erros->erro->__toString());
        }

        return $xmlResult->resultado->filtro ?? null;
    }

    /**
     * @return \nusoap_client
     */
    private function getNusoapClientExportacaoInstance(): \nusoap_client
    {
        if (!isset($this->nusoapClientExportacao)) {

            $endpoint = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class)
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

            $endpoint = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class)
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

    /**
     * Manda para a fila de integração todos os produtos com:
     *  - porcent_preench=100%,
     *  - com alteração posterior a última dt integração
     *  - e dt marcada integração null
     *
     * @param int|null $limit
     * @return int
     */
    public function enviarProdutosParaIntegracao(?int $limit = null): int
    {
        $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
        $sql = 'SELECT id FROM est_produto WHERE json_data->>"$.porcent_preench" > 0 AND ' .
            '(JSON_IS_NULL_OR_EMPTY(json_data, \'ecommerce_dt_integr\') OR json_data->>"$.ecommerce_dt_integr" <= updated) AND ' .
            '(JSON_IS_NULL_OR_EMPTY(json_data, \'ecommerce_dt_marcado_integr\'))';
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        $produtosParaIntegrar = $conn->fetchAll($sql);

        foreach ($produtosParaIntegrar as $rProduto) {
            $this->syslog->info('Enviar produto para integração', 'id = ' . $rProduto['id']);
            try {
                $conn->beginTransaction();
                $conn->executeUpdate('UPDATE est_produto SET json_data = json_set(json_data, \'$.ecommerce_dt_marcado_integr\', :dt) where id = :id',
                    [
                        'dt' => (new \DateTime())->format('d/m/Y H:i:s'),
                        'id' => $rProduto['id']
                    ]);
                $this->bus->dispatch(new IntegrarProdutoEcommerceMessage($rProduto['id']));
                $conn->commit();
            } catch (\Throwable $e) {
                try {
                    $conn->rollBack();
                } catch (ConnectionException $e) {
                    $this->syslog->info('Erro no rollback');
                }
            }

        }

        return count($produtosParaIntegrar);

    }


}
