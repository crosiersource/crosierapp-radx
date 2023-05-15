<?php


namespace App\Business\Ecommerce;

use App\Messenger\Ecommerce\Message\IntegrarProdutoEcommerceMessage;
use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Entity\Security\User;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ImageUtils\ImageUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
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
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Regras de negócio para a integração com a WebStorm.
 *
 * @author Carlos Eduardo Pauluk
 */
class IntegradorWebStorm implements IntegradorEcommerce
{

    private AppConfigEntityHandler $appConfigEntityHandler;

    private Security $security;

    private DeptoEntityHandler $deptoEntityHandler;

    private GrupoEntityHandler $grupoEntityHandler;

    private SubgrupoEntityHandler $subgrupoEntityHandler;

    private ProdutoEntityHandler $produtoEntityHandler;

    private VendaEntityHandler $vendaEntityHandler;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private ClienteEntityHandler $clienteEntityHandler;

    private UploaderHelper $uploaderHelper;

    private ParameterBagInterface $params;

    private MessageBusInterface $bus;

    private SyslogBusiness $syslog;

    private ?string $chave = null;

    private ?int $delayEntreIntegracoesDeProduto = null;

    private ?bool $permiteIntegrarProdutosSemImagem = null;

    private \nusoap_client $nusoapClientExportacao;

    private \nusoap_client $nusoapClientImportacao;

    private ?array $tiposCaracteristicasNaWebStorm = null;

    private ?array $deptosNaWebStorm = null;

    private ?array $marcasNaWebStorm = null;

    /**
     * IntegradorWebStorm constructor.
     * @param AppConfigEntityHandler $appConfigEntityHandler
     * @param Security $security
     * @param DeptoEntityHandler $deptoEntityHandler
     * @param GrupoEntityHandler $grupoEntityHandler
     * @param SubgrupoEntityHandler $subgrupoEntityHandler
     * @param ProdutoEntityHandler $produtoEntityHandler
     * @param VendaEntityHandler $vendaEntityHandler
     * @param VendaItemEntityHandler $vendaItemEntityHandler
     * @param ClienteEntityHandler $clienteEntityHandler
     * @param UploaderHelper $uploaderHelper
     * @param ParameterBagInterface $params
     * @param MessageBusInterface $bus
     * @param SyslogBusiness $syslog
     */
    public function __construct(AppConfigEntityHandler $appConfigEntityHandler,
                                Security               $security,
                                DeptoEntityHandler     $deptoEntityHandler,
                                GrupoEntityHandler     $grupoEntityHandler,
                                SubgrupoEntityHandler  $subgrupoEntityHandler,
                                ProdutoEntityHandler   $produtoEntityHandler,
                                VendaEntityHandler     $vendaEntityHandler,
                                VendaItemEntityHandler $vendaItemEntityHandler,
                                ClienteEntityHandler   $clienteEntityHandler,
                                UploaderHelper         $uploaderHelper,
                                ParameterBagInterface  $params,
                                MessageBusInterface    $bus,
                                SyslogBusiness         $syslog)
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->security = $security;
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->clienteEntityHandler = $clienteEntityHandler;
        $this->uploaderHelper = $uploaderHelper;
        $this->params = $params;
        $this->bus = $bus;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
    }


    /**
     * @return string
     */
    public function getChave(): string
    {
        if (!$this->chave) {
            try {
                $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
                $rs = $conn->fetchAssociative('SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                    [
                        'chave' => 'ecomm_info_integra_WEBSTORM_chave',
                        'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                    ]);
                $this->chave = $rs['valor'];
            } catch (\Throwable $e) {
                throw new \RuntimeException('Erro ao instanciar IntegradorWebStorm (chave ecomm_info_integra_WEBSTORM_chave ?)');
            }
        }
        return $this->chave;
    }

    /**
     * @return string
     */
    public function getDelayEntreIntegracoesDeProduto(): string
    {
        if ($this->delayEntreIntegracoesDeProduto === null) {
            try {
                $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
                $rs = $conn->fetchAssociative('SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                    [
                        'chave' => 'ecomm_info_delay_entre_integracoes_de_produto',
                        'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                    ]);
                $this->delayEntreIntegracoesDeProduto = (int)$rs['valor'];
            } catch (\Throwable $e) {
                $this->syslog->err('Erro ao pesquisar valor para "ecomm_info_delay_entre_integracoes_de_produto". Default para 0');
                $this->delayEntreIntegracoesDeProduto = 0;
            }
        }
        return $this->delayEntreIntegracoesDeProduto;
    }

    /**
     * @return string
     */
    public function isPermiteIntegrarProdutosSemImagens(): string
    {
        if ($this->permiteIntegrarProdutosSemImagem === null) {
            try {
                $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
                $rs = $conn->fetchAssociative('SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                    [
                        'chave' => 'ecomm_info_permite_integrar_produtos_sem_imagens',
                        'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                    ]);
                $this->permiteIntegrarProdutosSemImagem = filter_var($rs['valor'], FILTER_VALIDATE_BOOLEAN);
            } catch (\Throwable $e) {
                $this->syslog->err('Erro isPermiteIntegrarProdutosSemImagens". Default para false');
                $this->permiteIntegrarProdutosSemImagem = false;
            }
        }
        return $this->permiteIntegrarProdutosSemImagem;
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

            $xmlResult = simplexml_load_string(utf8_encode($arResultado));

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
            if (trim(mb_strtolower($marcaNaWebStorm['nome'])) === trim(mb_strtolower($marca))) {
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

            $xmlResult = simplexml_load_string(utf8_encode($arResultado));

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

            $xmlResult = simplexml_load_string(utf8_encode($arResultado));

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
            if (trim(mb_strtolower($tipoCaracteristicaNaWebStorm['nome'])) === trim(mb_strtolower($tipoCaracteristica))) {
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
            $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
            $rs = $conn->fetchAssociative('SELECT id, valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                [
                    'chave' => 'est_produto_json_metadata',
                    'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                ]);
            $jsonMetadata = json_decode($rs['valor'], true);
            $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['ecommerce_id'] = $idTipoCaracteristicaNaWebStorm;
            $jsonMetadata_encoded = json_encode($jsonMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $conn->update('cfg_app_config', ['valor' => $jsonMetadata_encoded], ['id' => $rs['id']]);
            $this->syslog->info('integraTipoCaracteristica: OK', $syslog_obs);
        } catch (\Throwable $e) {
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
                if (trim(mb_strtolower($caracteristicaNaWebStorm['nome'])) === trim(mb_strtolower($caracteristica))) {
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
        $this->syslog->info('Iniciando... integrarDeptosGruposSubgrupos');
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
                    <chave>' . $this->getChave() . '</chave>
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
            if (trim(mb_strtolower($deptoNaWebStorm['nome'])) === trim(mb_strtolower($depto->nome))) {
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
            $depto->jsonData['ecommerce_id'] = $idDeptoWebStorm;
            $depto->jsonData['integrado_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $depto->jsonData['integrado_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';
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
            if (trim(mb_strtolower($grupoNaWebStorm['nome'])) === trim(mb_strtolower($grupo->nome))) {
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
            $grupo->jsonData['ecommerce_id'] = $idGrupoWebStorm;
            $grupo->jsonData['integrado_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $grupo->jsonData['integrado_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';

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
        $syslog_obs = 'subgrupo [codigo="' . $subgrupo->codigo . '", nome="' . $subgrupo->nome . '", id ="' . $subgrupo->getId() . '"]';
        $this->syslog->info('integraSubgrupo - ini', $syslog_obs);
        if (!$subgrupo->codigo || !$subgrupo->nome) {
            $this->syslog->info('subgrupo sem código ou nome não pode ser integrado', $syslog_obs);
            return 0;
        }

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
            if (trim(mb_strtolower($subgrupoNaWebStorm['nome'])) === trim(mb_strtolower($subgrupo->nome))) {
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
            $subgrupo->jsonData['ecommerce_id'] = $idSubgrupoWebStorm;
            $subgrupo->jsonData['integrado_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $subgrupo->jsonData['integrado_por'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : 'n/d';
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
    public function integraDeptoGrupoSubgrupo(string $descricao, int $nivel, ?int $idNivelPai1 = null, ?int $idNivelPai2 = null, ?int $ecommerce_id = null)
    {
        $descricao = trim($descricao);
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
     * @param bool|null $respeitarDelay
     * @return void
     * @throws ViewException
     */
    public function integraProduto(Produto $produto, ?bool $integrarImagens = true, ?bool $respeitarDelay = false, ?bool $reintegrarRecursivo = true): void
    {
        $syslog_obs = 'produto = ' . $produto->getId() . '; integrarImagens = ' . $integrarImagens;

        if (!$this->isPermiteIntegrarProdutosSemImagens() && $produto->imagens->count() < 1) {
            $this->syslog->info('integraProduto - Não é permitido integrar produto sem imagens', $syslog_obs);
            throw new ViewException('Não é permitido integrar produto sem imagens');
        }
        if ($respeitarDelay) {
            if ($this->getDelayEntreIntegracoesDeProduto()) {
                $this->syslog->info('integraProduto - delay de ' . $this->getDelayEntreIntegracoesDeProduto(), $syslog_obs);
                sleep($this->getDelayEntreIntegracoesDeProduto());
            } else {
                $this->syslog->info('integraProduto - sem delay entre integrações');
            }
        }

        $start = microtime(true);

        $this->syslog->info('integraProduto - ini', $syslog_obs);

        $preco = $produto->jsonData['preco_site'] ?? $produto->jsonData['preco_tabela'] ?? 0.0;
        if ($preco <= 0) {
            $err = 'Não é possível integrar produto com preço <= 0';
            $this->syslog->err($err, $syslog_obs);
            throw new \RuntimeException($err);
        }

        try {
            $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
            $rs = $conn->fetchAssociative('SELECT valor FROM cfg_app_config WHERE chave = :chave AND app_uuid = :appUUID',
                [
                    'chave' => 'est_produto_json_metadata',
                    'appUUID' => $_SERVER['CROSIERAPPRADX_UUID']
                ]);
            $jsonCampos = json_decode($rs['valor'], true)['campos'];
        } catch (\Throwable $e) {
            $err = 'Erro ao pesquisar est_produto_json_metadata';
            $this->syslog->err($err, $syslog_obs);
            throw new \RuntimeException($err);
        }

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
            // '<situacao>0</situacao>' .
            '<prazoXD>0</prazoXD>' .
            '<conjunto />' .
            '<nome>' . $produto->jsonData['titulo'] . '</nome>' .
            '<referencia>' . strtoupper($produto->jsonData['referencia'] ?? '') . '</referencia>';

        $descricao_produto = '';
        if ($produto->jsonData['descricao_produto'] ?? false) {
            $descricao_produto = htmlspecialchars($produto->jsonData['descricao_produto']);
        }
        $xml .= '<descricao>' . $descricao_produto . '</descricao>';

        $caracteristicas = '';
        if ($produto->jsonData['caracteristicas'] ?? false) {
            $caracteristicas = htmlspecialchars($produto->jsonData['caracteristicas']);
        }
        $xml .= '<descricao-descricao-caracteristicas>' . $caracteristicas . '</descricao-descricao-caracteristicas>';

        $itens_inclusos = '';
        if ($produto->jsonData['itens_inclusos'] ?? false) {
            $itens_inclusos = htmlspecialchars($produto->jsonData['itens_inclusos']);
        }
        $xml .= '<descricao-itens-inclusos>' . $itens_inclusos . '</descricao-itens-inclusos>';

        $compativel_com = '';
        if ($produto->jsonData['compativel_com'] ?? false) {
            $compativel_com = htmlspecialchars($produto->jsonData['compativel_com']);
        }
        $xml .= '<descricao-compativel-com>' . $compativel_com . '</descricao-compativel-com>';

        $especif_tec = '';
        if ($produto->jsonData['especif_tec'] ?? false) {
            $especif_tec = htmlspecialchars($produto->jsonData['especif_tec']);
        }
        $xml .= '<descricao-especificacoes-tecnicas>' . $especif_tec . '</descricao-especificacoes-tecnicas>';

        $ecommerceId_caracteristica_jaAdicionadas = [];
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
                        if (!in_array($ecommerceId_caracteristica, $ecommerceId_caracteristica_jaAdicionadas, true)) {
                            $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                            $ecommerceId_caracteristica_jaAdicionadas[] = $ecommerceId_caracteristica;
                        }
                    }
                } else {
                    $ecommerceId_caracteristica = $this->integraCaracteristica($ecommerceId_tipoCaracteristica, $valor);
                    if (!in_array($ecommerceId_caracteristica, $ecommerceId_caracteristica_jaAdicionadas, true)) {
                        $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                        $ecommerceId_caracteristica_jaAdicionadas[] = $ecommerceId_caracteristica;
                    }
                }
            }
        }

        $ecommerceId_caracteristica_unidade = $produto->unidadePadrao->jsonData['webstorm_info']['caracteristica_id'] ?? null;
        if (!$ecommerceId_caracteristica_unidade) {
            throw new ViewException('Erro ao integrar unidade do produto');
        }
        $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica_unidade . '</idCaracteristica></caracteristicaProduto>';

        if ($integrarImagens) {
            foreach ($produto->imagens as $imagem) {
                $url = $_SERVER['CROSIERAPPRADX_URL'] . $this->uploaderHelper->asset($imagem, 'imageFile');
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
                            $file1080 = $_SERVER['PASTA_FOTOS_PRODUTOS'] .
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
                    throw new ViewException($err);
                }

                $xml .= '<imagens>
				<url>' . $url1080 . '</url>
				<prioridade>' . ($imagem->getOrdem() - 1) . '</prioridade>
			</imagens>';
            }
        }

        $referenciasExtras = '';
        if ($produto->jsonData['referencias_extras'] ?? false) {
            $referenciasExtras = htmlspecialchars($produto->jsonData['referencias_extras']);
        }
        $xml .= '<referenciasExtras>' . $referenciasExtras . '</referenciasExtras>';


        $xml .=
            '<itensVenda>
				<idItemVenda>' . $produtoItemVendaId . '</idItemVenda>
				<codigo>' . $produto->getId() . '</codigo>
				<preco>' . $preco . '</preco>
				<estoque>' . ($produto->jsonData['qtde_estoque_total'] ?? 0) . '</estoque>
				<estoqueMin>0</estoqueMin>
				<situacao>1</situacao>
				<peso>' . ($produto->jsonData['peso'] ?? '') . '</peso>
				<ean>' . htmlspecialchars(isset($produto->jsonData['ean']) ? $produto->jsonData['ean'] : '') . '</ean>
				<altura>' . $altura . '</altura>
				<largura>' . $largura . '</largura>
				<comprimento>' . $comprimento . '</comprimento>
            </itensVenda></produto>' .
            '</ws_integracao>]]>';


        $this->syslog->info('integraProduto - XML REQUEST - ' . $syslog_obs, $xml);

        $client = $this->getNusoapClientImportacaoInstance();

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

        $this->syslog->info('integraProduto - XML RESPONSE - ' . $syslog_obs, $xml);

        $xmlResult = simplexml_load_string($arResultado);

        if ($xmlResult->erros->erro ?? false) {
            $erro = $xmlResult->erros->erro->__toString();
            if (strpos($erro, 'Erro: Foi encontrado produto com o idProduto') === 0) {
                $this->syslog->info($erro, $syslog_obs);
                $ecommerceIdCorreto = (int)substr($erro, 46);
                $this->corrigirVinculosCrosierWebStorm($produto, $ecommerceIdCorreto);
                if ($reintegrarRecursivo) {
                    $this->syslog->info('Reintegrando recursivo...', $syslog_obs);
                    $this->integraProduto($produto, $integrarImagens, $respeitarDelay, false);
                    return;
                }
            }
            $this->syslog->err('integraProduto - erros: ' . $erro, $syslog_obs);
            throw new \RuntimeException($xmlResult->erros->erro->__toString());
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $integradoEm = (new \DateTime())->modify('+1 minutes')->format('Y-m-d H:i:s');
        // está fazendo UPDATE
        if ($produtoEcommerceId) {
            $ecommerceId = (int)$xmlResult->produtos->produto->idProduto->__toString();
            if ($ecommerceId >= 1) {
                $produto->jsonData['ecommerce_id'] = $ecommerceId;
            } else {
                throw new ViewException('ecommerceId não encontrada no xmlResult');
            }
            $ecommerceItemVendaId = (int)$xmlResult->produtos->produto->itensVenda->itemVenda->resultado->idItemVenda->__toString();
            if ($ecommerceItemVendaId >= 1) {
                $produto->jsonData['ecommerce_item_venda_id'] = $ecommerceItemVendaId;
            } else {
                throw new ViewException('ecommerceItemVendaId não encontrada no xmlResult');
            }
        } else {
            $ecommerceId = (int)$xmlResult->produto->produto->idProduto->__toString();
            if ($ecommerceId >= 1) {
                $produto->jsonData['ecommerce_id'] = $ecommerceId;
            } else {
                throw new ViewException('ecommerceId não encontrada no xmlResult');
            }
            $ecommerceItemVendaId = (int)$xmlResult->produto->produto->itensVenda->itemVenda->idItemVenda->__toString();
            if ($ecommerceItemVendaId >= 1) {
                $produto->jsonData['ecommerce_item_venda_id'] = $ecommerceItemVendaId;
            } else {
                throw new ViewException('ecommerceItemVendaId não encontrada no xmlResult');
            }
            $produto->jsonData['ecommerce_dt_primeira_integracao'] = $integradoEm;
            $produto->jsonData['ecommerce_integrado_primeiro_por'] = $user ? $user->nome : 'n/d';
        }


        $produto->jsonData['ecommerce_dt_integr'] = $integradoEm;
        $produto->jsonData['ecommerce_dt_marcado_integr'] = null;
        $produto->jsonData['ecommerce_desatualizado'] = 0;

        $produto->jsonData['ecommerce_integr_por'] = $user ? $user->nome : 'n/d';


        $this->syslog->info('integraProduto - save', $syslog_obs);
        $this->produtoEntityHandler->save($produto);

        $tt = (int)(microtime(true) - $start);
        $this->syslog->info('integraProduto - OK (em ' . $tt . ' segundos)', $syslog_obs);
    }

    /**
     * Faz a integração de vários produtos em uma única chamada.
     *
     * @param array $produtosIds
     * @return void
     * @throws ViewException
     */
    public function atualizaEstoqueEPrecos(array $produtosIds): void
    {
        try {
            $start = microtime(true);
            $this->syslog->info('atualizaEstoqueEPrecos - ini');
            $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
            $conn->beginTransaction();
            $xml = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?>
                <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                   <chave>' . $this->getChave() . '</chave>
                   <acao>update</acao>
                   <modulo>produto</modulo>';
            $stmt = $conn->prepare('SELECT * FROM est_produto WHERE id = :id');

            $temAtualizacao = false;
            foreach ($produtosIds as $produtoId) {
                $stmt->bindValue('id', $produtoId);
                $produto = $stmt->executeQuery()->fetchAssociative();

                $this->syslog->info('atualizando estoque para o produto (id: ' . $produto['id'] . ')');

                $jsonData = json_decode($produto['json_data'], true);
                $produtoEcommerceId = $jsonData['ecommerce_id'] ?? null;
                $produtoItemVendaId = $jsonData['ecommerce_item_venda_id'] ?? null;
                if (!$produtoEcommerceId) {
                    $this->syslog->info('Produto sem jsonData[\'ecommerce_id\']');
                    continue;
                }
                if (!$produtoItemVendaId) {
                    $err = 'Produto com jsonData[\'ecommerce_id\'] mas sem jsonData[\'ecommerce_item_venda_id\']';
                    $this->syslog->err($err);
                    continue;
                }
                $preco = ($jsonData['preco_site'] ?? $jsonData['preco_tabela'] ?? 0.0);
                $estoque = ($jsonData['qtde_estoque_total'] ?? 0);

                $xml .= '<produto pk="idProduto">' .
                    '<idProduto>' . $produtoEcommerceId . '</idProduto>
                    <itensVenda> 
                    <idItemVenda>' . $produtoItemVendaId . '</idItemVenda>
                    <preco>' . $preco . '</preco>
                    <estoque>' . $estoque . '</estoque>
                </itensVenda></produto>';
                $temAtualizacao = true;

                $conn->insert('cfg_entity_change', [
                    'entity_class' => Produto::class,
                    'entity_id' => $produtoId,
                    'changing_user_id' => 1,
                    'changed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'changes' => 'Preço: ' . $preco . ', Estoque: ' . $estoque,
                    'obs' => 'ATUALIZANDO SALDO NA WEBSTORM (via atualizarTodosOsEstoquesEPrecos)',
                ]);

            }

            if (!$temAtualizacao) {
                $this->syslog->info('atualizaEstoqueEPrecos - OK (sem atualizações)');
                return;
            }
            $xml .= '</ws_integracao>]]>';

            $this->syslog->info('atualizaEstoqueEPrecos - enviando o XML', $xml);

            $client = $this->getNusoapClientImportacaoInstance();
            $arResultado = $client->call('precoEstoque', [
                'xml' => utf8_decode($xml)
            ]);
            if ($client->faultcode) {
                $this->syslog->err('atualizaEstoqueEPrecos - faultcode: ' . (string)$client->faultcode);
                throw new \RuntimeException($client->faultcode);
            }
            // else

            if ($client->getError()) {
                $this->syslog->err('atualizaEstoqueEPrecos - faultcode: ' . $client->getError());
                throw new \RuntimeException($client->getError());
            }

            $arResultado = utf8_encode($arResultado);
            $arResultado = str_replace('&nbsp;', ' ', $arResultado);

            $this->syslog->info('atualizaEstoqueEPrecos - XML RESPONSE - ', $xml);

            $xmlResult = simplexml_load_string($arResultado);

            if ($xmlResult->erros->erro ?? false) {
                $this->syslog->err('atualizaEstoqueEPrecos - erros: ' . $xmlResult->erros->erro->__toString());
                throw new \RuntimeException($xmlResult->erros->erro->__toString());
            }

            $tt = (int)(microtime(true) - $start);
            $conn->commit();
            $this->syslog->info('atualizaEstoqueEPrecos - OK (em ' . $tt . ' segundos)');
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    $msg = ExceptionUtils::treatException($e);
                    $this->syslog->err($msg, $e->getTraceAsString());
                }
            }
            $errMsg = 'atualizaEstoqueEPrecos - ERRO (' . $e->getMessage() . ')';
            if ($e instanceof ViewException) {
                $errMsg .= ' - ' . $e->getMessage();
            }
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }

    /**
     * Envia para a fila de integração os produtos que foram alterados mas que ainda não foram reintegrados no ecommerce.
     * @return int
     * @throws ViewException
     */
    public function reenviarParaIntegracaoProdutosAlterados(): int
    {
        try {
            $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();

            $sql = 'SELECT id FROM est_produto WHERE ' .
                'not JSON_IS_NULL_OR_EMPTY_OR_ZERO(json_data, \'ecommerce_id\') AND ' .
                'not JSON_IS_NULL_OR_EMPTY_OR_ZERO(json_data, \'ecommerce_item_venda_id\') AND ' .
                'json_data->>"$.porcent_preench" > 0 AND ' .
                'json_data->>"$.ecommerce_desatualizado" = 1 AND ' .
                'JSON_IS_NULL_OR_EMPTY(json_data, \'ecommerce_dt_marcado_integr\')';
            $rProdutos = $conn->fetchAllAssociative($sql);

            $this->syslog->info('reenviarParaIntegracaoProdutosAlterados() - ' . count($rProdutos) . ' produtos a reintegrar');

            if (count($rProdutos) === 0) {
                return 0;
            }

            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);

            foreach ($rProdutos as $rProduto) {
                try {
                    $produto = $repoProduto->find($rProduto['id']);
                    $produto->jsonData['ecommerce_dt_marcado_integr'] = (new \DateTime())->format('d/m/Y H:i:s');
                    $this->produtoEntityHandler->save($produto);
                    $this->bus->dispatch(new IntegrarProdutoEcommerceMessage($rProduto['id']));
                    $this->syslog->info('Produto reenviado para integração (id = "' . $rProduto['id'] . '"');
                } catch (\Throwable $e) {
                    $this->syslog->err('reenviarParaIntegracaoProdutosAlterados() - Erro ao enviar produto (id = "' . $rProduto['id'] . '")', $e->getMessage() . '\n\n' . $e->getTraceAsString());
                    try {
                        if ($conn->isTransactionActive()) {
                            $conn->rollBack();
                        }
                    } catch (ConnectionException $e) {
                        $this->syslog->err('reenviarParaIntegracaoProdutosAlterados() - Erro no rollback');
                    }
                }
            }
            return count($rProdutos);
        } catch (\Throwable $e) {
            $this->syslog->err('Erro ao reenviarParaIntegracaoProdutosAlterados()', $e->getMessage() . '\n\n' . $e->getTraceAsString());
            throw new ViewException('Erro ao reenviarParaIntegracaoProdutosAlterados()');
        }
    }

    /**
     * Envia produtos para a fila (queue) que executará as integrações com o webstorm.
     *
     * @param int|null $limit
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function reenviarTodosOsProdutosParaIntegracao(?int $limit = null): int
    {
        $this->syslog->info('reenviarProdutosParaIntegracao');
        $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
        $sql = 'SELECT id FROM est_produto WHERE not JSON_IS_NULL_OR_EMPTY_OR_ZERO(json_data,\'ecommerce_id\')';
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        $produtosParaIntegrar = $conn->fetchAllAssociative($sql);

        /** @var ProdutoRepository $repoProduto */
        $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);

        foreach ($produtosParaIntegrar as $rProduto) {
            $produto = $repoProduto->find($rProduto['id']);
            $this->syslog->info('Enviar produto para integração', 'id = ' . $rProduto['id']);
            try {
                $produto->jsonData['ecommerce_dt_marcado_integr'] = (new \DateTime())->format('d/m/Y H:i:s');
                $this->produtoEntityHandler->save($produto);

                $this->bus->dispatch(new IntegrarProdutoEcommerceMessage($rProduto['id']));
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


    /**
     * Atualiza as qtdes de estoque e preços para todos os produtos.
     *
     * @param int|null $limit
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function atualizarTodosOsEstoquesEPrecos(?int $limit = null): int
    {
        $this->syslog->info('atualizarTodosOsEstoquesEPrecos');
        $conn = $this->produtoEntityHandler->getDoctrine()->getConnection();
        $sql = 'SELECT id FROM est_produto WHERE not JSON_IS_NULL_OR_EMPTY_OR_ZERO(json_data,\'ecommerce_id\')';
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        $rs = $conn->fetchAllAssociative($sql);
        $produtosIds = [];

        $enviarNoMax = 100;
        $i = 0;
        foreach ($rs as $r) {
            $produtosIds[] = $r['id'];
            if ($i++ > $enviarNoMax) {
                $this->atualizaEstoqueEPrecos($produtosIds);
                $produtosIds = [];
                $i = 0;
            }
        }

        $this->syslog->info('Atualizando qtdes/preços para ' . count($produtosIds) . ' produto(s)');


        return count($produtosIds);
    }


    /**
     * @param \DateTime $dtVenda
     * @param bool|null $resalvar
     * @return int
     */
    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int
    {
        $this->syslog->info('Iniciando... obterVendas para o dia ' . $dtVenda->format('d/m/Y') . '... (resalvar? ' . ($resalvar ? 'SIM' : 'NÃO') . ')');
        $pedidos = $this->obterVendasPorData($dtVenda);
        $i = 0;
        if ($pedidos->pedido ?? false) {
            foreach ($pedidos->pedido as $pedido) {
                try {
                    $this->integrarVendaParaCrosier($pedido, (int)$pedido->status === 2 || $resalvar);
                } catch (ViewException $e) {
                    $this->syslog->err('Erro ao integrarVendaParaCrosier - pedido: ' . $pedido->idPedido->__toString(), $e->getMessage() . '\n\n' . $e->getTraceAsString());
                    continue;
                }
                $i++;
            }
        }
        return $i;
    }

    /**
     * @param \DateTime $dtVenda
     * @return \SimpleXMLElement|void|null
     */
    public function obterVendasPorData(\DateTime $dtVenda)
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
                    </filtro>
                    </ws_integracao>]]>';

        $this->syslog->info('CHAMADA: Obtendo vendas entre ' . $dtIni->format('d/m/Y H:i:s') . ' e ' . $dtFim->format('d/m/Y H:i:s'), $xml);


        $client = $this->getNusoapClientExportacaoInstance();

        try {
            $arResultado = $client->call('pedidoSelect', [
                'xml' => utf8_decode($xml)
            ]);
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            $this->syslog->info('ERRO ao obter vendas entre ' . $dtIni->format('d/m/Y H:i:s') . ' e ' . $dtFim->format('d/m/Y H:i:s') . '. Mensagem: ' . $msg, $e->getTraceAsString());
        }

        if ($client->faultcode) {
            $this->syslog->info('ERRO ao obter vendas entre ' . $dtIni->format('d/m/Y H:i:s') . ' e ' . $dtFim->format('d/m/Y H:i:s') . '. faultCode: ' . $client->faultcode, $arResultado);
            throw new \RuntimeException($client->faultcode);
        }
        if ($client->getError()) {
            $this->syslog->info('ERRO ao obter vendas entre ' . $dtIni->format('d/m/Y H:i:s') . ' e ' . $dtFim->format('d/m/Y H:i:s') . '. error: ' . $client->getError(), $arResultado);
            throw new \RuntimeException($client->getError());
        }

        $this->syslog->info('RETORNO: Obtendo vendas entre ' . $dtIni->format('d/m/Y H:i:s') . ' e ' . $dtFim->format('d/m/Y H:i:s'), $arResultado);

        $xmlResult = simplexml_load_string($arResultado);

        if ($xmlResult->erros ?? false) {
            throw new \RuntimeException($xmlResult->erros->erro->__toString());
        }

        return $xmlResult->resultado->filtro ?? null;
    }


    /**
     * @param int $idPedido
     * @return \SimpleXMLElement|void|null
     */
    public function obterVendaPorId(int $idPedido, ?bool $resalvar = false)
    {
        $xml = '<![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>
                    <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
                    <acao>select</acao>
                    <modulo>pedido</modulo>    
                    <filtro>
                        <idPedido>' . $idPedido . '</idPedido>
                        <idCliente></idCliente>
                        <cpf_cnpj></cpf_cnpj>
                        <dataInicial></dataInicial>
                        <dataFinal></dataFinal>
                        <status></status>
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

        $pedido = $xmlResult->resultado->filtro->pedido;
        $this->integrarVendaParaCrosier($pedido, (int)$pedido->status === 2 || $resalvar);
        return true;
    }


    /**
     * @param int $idClienteEcommerce
     * @return \SimpleXMLElement|null
     */
    public function obterCliente($idClienteEcommerce)
    {
        $xml = '<![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>    
                    <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
                    <acao>select</acao>
                    <modulo>cliente</modulo>    
                    <filtro>
                            <idCliente>' . $idClienteEcommerce . '</idCliente>   	 
                    </filtro>
                </ws_integracao>]]>';

        $client = $this->getNusoapClientExportacaoInstance();

        $arResultado = $client->call('clienteSelect', [
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

        return $xmlResult->resultado->filtro->cliente ?? null;


    }

    /**
     * @param \SimpleXMLElement $pedido
     * @param bool|null $resalvar
     * @throws ViewException
     */
    private function integrarVendaParaCrosier(\SimpleXMLElement $pedido, ?bool $resalvar = false): void
    {
        $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();

        try {
            $dtPedido = DateTimeUtils::parseDateStr($pedido->dataPedido->__toString());

            $this->syslog->info('Integrando pedido ' . $pedido->idPedido->__toString() . ' de ' .
                $dtPedido->format('d/m/Y H:i:s') . ' Cliente: ' . $pedido->cliente->nome->__toString());

            $venda = $conn->fetchAllAssociative('SELECT * FROM ven_venda WHERE json_data->>"$.ecommerce_idPedido" = :ecommerce_idPedido',
                ['ecommerce_idPedido' => $pedido->idPedido]);
            $venda = $venda[0] ?? null;
            if ($venda) {
                // se já existe, só confere o status
                // O único status que pode ser alterado no sentido WebStorm -> Crosier é quando está em 'Aguardando Pagamento'
                $vendaJsonData = json_decode($venda['json_data'], true);
                if (($vendaJsonData['ecommerce_status_descricao'] === 'Aguardando Pagamento') &&
                    (($vendaJsonData['ecommerce_status'] ?? null) != $pedido->status->__toString())) {

                    $vendaJsonData['ecommerce_status'] = $pedido->status->__toString();
                    $vendaJsonData['ecommerce_status_descricao'] = $pedido->desStatus->__toString();
                    $venda_['json_data'] = json_encode($vendaJsonData);
                    try {
                        $conn->update('ven_venda', $venda_, ['id' => $venda['id']]);
                    } catch (\Exception $e) {
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
                    $this->syslog->err($erro);
                    throw new \RuntimeException($erro);
                }
                /** @var VendaRepository $repoVenda */
                $repoVenda = $this->vendaEntityHandler->getDoctrine()->getRepository(Venda::class);
                $venda = $repoVenda->find($venda['id']);

            } else {
                $venda = new Venda();
            }

            $venda->dtVenda = $dtPedido;

            /** @var ColaboradorRepository $repoColaborador */
            $repoColaborador = $this->vendaEntityHandler->getDoctrine()->getRepository(Colaborador::class);
            $vendedorNaoIdentificado = $repoColaborador->findOneBy(['cpf' => '99999999999']);
            $venda->vendedor = $vendedorNaoIdentificado;

            $venda->status = 'PV ABERTO';


            $cliente = $conn->fetchAllAssociative('SELECT id FROM crm_cliente WHERE documento = :documento',
                ['documento' => $pedido->cliente->cpf_cnpj->__toString()]);
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->vendaEntityHandler->getDoctrine()->getRepository(Cliente::class);
            if ($cliente[0]['id'] ?? false) {
                $cliente = $repoCliente->find($cliente[0]['id']);
            } else {
                $cliente = null;
            }

            if (!$cliente || $resalvar) {

                $clienteEcommerce = $this->obterCliente((int)$pedido->cliente->idCliente);

                $cliente = $cliente ?? new Cliente();
                if ($clienteEcommerce->cpf->__toString()) {
                    $cliente->documento = $clienteEcommerce->cpf->__toString();
                    $cliente->nome = $clienteEcommerce->nome->__toString();
                    $cliente->jsonData['tipo_pessoa'] = 'PF';
                    $cliente->jsonData['rg'] = $clienteEcommerce->rg->__toString();
                    $cliente->jsonData['dtNascimento'] = $clienteEcommerce->dataNascimento_dataCriacao->__toString();
                    $cliente->jsonData['sexo'] = $clienteEcommerce->sexo->__toString();
                } else {
                    $cliente->documento = $clienteEcommerce->cnpj->__toString();
                    $cliente->nome = $clienteEcommerce->razaoSocial->__toString();
                    $cliente->jsonData['tipo_pessoa'] = 'PJ';
                    $cliente->jsonData['nome_fantasia'] = $clienteEcommerce->nomeFantasia->__toString();
                    $cliente->jsonData['inscricao_estadual'] = preg_replace("/[^0-9]/", "", $clienteEcommerce->inscricaoEstadual->__toString());
                }

                $cliente->jsonData['fone1'] = $clienteEcommerce->telefone1->__toString();
                $cliente->jsonData['fone2'] = $clienteEcommerce->telefone2->__toString();

                $cliente->jsonData['email'] = $clienteEcommerce->email->__toString();
                $cliente->jsonData['canal'] = 'ECOMMERCE';
                $cliente->jsonData['ecommerce_id'] = $clienteEcommerce->idCliente->__toString();

                $cliente->jsonData['montadora'] = $clienteEcommerce->montadora->__toString();
                $cliente->jsonData['modelo'] = $clienteEcommerce->modelo->__toString();
                $cliente->jsonData['ano'] = $clienteEcommerce->ano->__toString();

                $cliente = $this->clienteEntityHandler->save($cliente);
            }

            // Verifica os endereços do cliente
            $enderecoJaSalvo = false;
            if (($cliente->jsonData['enderecos'] ?? false) && count($cliente->jsonData['enderecos']) > 0) {
                foreach ($cliente->jsonData['enderecos'] as $endereco) {
                    if ((($endereco['tipo'] ?? '') === 'ENTREGA,FATURAMENTO') &&
                        (($endereco['logradouro'] ?? '') === $pedido->entrega->logradouro->__toString()) &&
                        (($endereco['numero'] ?? '') === $pedido->entrega->numero->__toString()) &&
                        (($endereco['complemento'] ?? '') === $pedido->entrega->complemento->__toString()) &&
                        (($endereco['bairro'] ?? '') === $pedido->entrega->bairro->__toString()) &&
                        (($endereco['cep'] ?? '') === $pedido->entrega->cep->__toString()) &&
                        (($endereco['cidade'] ?? '') === $pedido->entrega->cidade->__toString()) &&
                        (($endereco['estado'] ?? '') === $pedido->entrega->estado->__toString())) {
                        $enderecoJaSalvo = true;
                    }
                }
            }
            if (!$enderecoJaSalvo) {
                $cliente->jsonData['enderecos'][] = [
                    'tipo' => 'ENTREGA,FATURAMENTO',
                    'logradouro' => $pedido->entrega->logradouro->__toString(),
                    'numero' => $pedido->entrega->numero->__toString(),
                    'complemento' => $pedido->entrega->complemento->__toString(),
                    'bairro' => $pedido->entrega->bairro->__toString(),
                    'cep' => $pedido->entrega->cep->__toString(),
                    'cidade' => $pedido->entrega->cidade->__toString(),
                    'estado' => $pedido->entrega->estado->__toString(),
                ];
                $cliente = $this->clienteEntityHandler->save($cliente);
            }


            $venda->cliente = $cliente;

            $venda->jsonData['canal'] = 'ECOMMERCE';
            $venda->jsonData['ecommerce_idPedido'] = $pedido->idPedido->__toString();
            $venda->jsonData['ecommerce_status'] = $pedido->status->__toString();

            $obs = [];
            $venda->jsonData['ecommerce_entrega_retirarNaLoja'] = ($pedido->entrega->retirarLoja->__toString() ?? null);
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
            $venda->jsonData['ecommerce_status'] = $pedido->status->__toString();
            $venda->jsonData['ecommerce_status_descricao'] = $pedido->desStatus->__toString();

            if ($pedido->entrega->retirarLoja ?? false) {
                if ((int)$pedido->entrega->retirarLoja->__toString() === 1) {
                    $obs[] = '* ' . $pedido->entrega->formaEntrega->__toString() . ' *';
                    if ($pedido->entrega->agendamento ?? false) {
                        try {
                            $obs[] = 'Agendado para: ' . DateTimeUtils::parseDateStr($pedido->entrega->agendamento->__toString())->format('d/m/Y H:i');
                        } catch (\Throwable $e) {
                            $this->syslog->err('integrarVendaParaCrosier - ' . $e->getMessage() . ' ...continuando');
                        }
                    }
                    $obs[] = '';
                }
            }

            $obs[] = 'IP: ' . ($pedido->ip ?? null);
            $obs[] = 'Pagamento: ' . ($pedido->pagamentos->pagamento->tipoFormaPagamento->__toString() ?? null) . ' - ' . ($pedido->pagamentos->pagamento->nomeFormaPagamento->__toString() ?? null);
            $obs[] = 'Desconto: ' . number_format($pedido->pagamentos->pagamento->desconto->__toString(), 2, ',', '.');
            $obs[] = 'Parcelas: ' . ($pedido->pagamentos->pagamento->parcelas->__toString() ?? null);
            $obs[] = 'Valor Parcela: R$ ' . number_format($pedido->pagamentos->pagamento->valorParcela->__toString(), 2, ',', '.');


            $venda->jsonData['obs'] = implode(PHP_EOL, $obs);

            $venda->subtotal = 0.0;// a ser recalculado posteriormente
            $venda->desconto = 0.0;// a ser recalculado posteriormente
            $venda->valorTotal = 0.0;// a ser recalculado posteriormente


            $conn->beginTransaction();
            /** @var ProdutoRepository $repoProduto */
            $repoProduto = $this->produtoEntityHandler->getDoctrine()->getRepository(Produto::class);
            $totalProdutos = 0.0;
            $produtosNoCrosier = [];
            foreach ($pedido->produtos->produto as $produtoWebStorm) {

                /** @var Produto $produto */
                $produto = null;
                try {
                    // verifica se já existe uma ven_venda com o json_data.ecommerce_idPedido
                    $sProduto = $conn->fetchAssociative(
                        'SELECT id FROM est_produto 
                                    WHERE 
                                    json_data->>"$.ecommerce_id" = :idProduto AND 
                                    json_data->>"$.ecommerce_item_venda_id" = :idItemVenda',
                        [
                            'idProduto' => $produtoWebStorm->idProduto->__toString(),
                            'idItemVenda' => $produtoWebStorm->idItemVenda->__toString(),
                        ]
                    );

                    if (!isset($sProduto['id'])) {
                        // Tenta achar apenas pelo nosso id
                        // (e na sequência já corrige os json_data->>"$.ecommerce_id" e json_data->>"$.ecommerce_item_venda_id"
                        // pois estava dando muito erro de integração, como se a WebStorm integrasse o produto mas não
                        // retornasse os ids corretos. 
                        $sProduto = $conn->fetchAssociative('SELECT id, json_data FROM est_produto WHERE id = :codigo',
                            ['codigo' => $produtoWebStorm->codigo->__toString()]);
                        if ($sProduto) {
                            $jsonData = json_decode($sProduto['json_data'], true);
                            $jsonData['ecommerce_id'] = $produtoWebStorm->idProduto->__toString();
                            $jsonData['ecommerce_item_venda_id'] = $produtoWebStorm->idItemVenda->__toString();
                            $conn->update('est_produto', ['json_data' => json_encode($jsonData)], ['id' => $sProduto['id']]);
                        } else {
                            throw new \RuntimeException();
                        }
                    }
                    $produto = $repoProduto->find($sProduto['id']);
                } catch (\Throwable $e) {
                    $msg = 'Erro ao integrar venda (Id: ' . $pedido->idPedido->__toString() . '). Erro ao pesquisar produto (idProduto = ' . $produtoWebStorm->idProduto->__toString() . ')';
                    $this->syslog->err($msg);
                    throw new ViewException($msg);
                }
                $produtosNoCrosier[$produtoWebStorm->idProduto->__toString()] = $produto; // RTA: dinâmico, para ser acessado no próximo foreach
                $valorProduto = $produtoWebStorm->valorUnitario; // $produto->jsonData['preco_site'];

                $totalProdutos = bcadd($totalProdutos, bcmul($produtoWebStorm->quantidade, $valorProduto, 2), 2);
            }// Salvo aqui para poder pegar o id
            $this->vendaEntityHandler->save($venda);
            $ordem = 1;
            $i = 0;
            $descontoAcum = 0.0;
            $vendaItem = null;
            foreach ($pedido->produtos->produto as $produtoWebStorm) {

                $produto = $produtosNoCrosier[$produtoWebStorm->idProduto->__toString()];

                $vendaItem = new VendaItem();
                $venda->addItem($vendaItem);
                $vendaItem->descricao = $produto->nome;
                if ($produto->jsonData['erp_codigo'] ?? false) {
                    $vendaItem->descricao .= ' (' . $produto->jsonData['erp_codigo'] . ')';
                }
                $vendaItem->ordem = $ordem++;
                $vendaItem->devolucao = false;

                $vendaItem->unidade = $produto->unidadePadrao;

                $vendaItem->precoVenda = $produtoWebStorm->valorUnitario->__toString(); // $produto->jsonData['preco_site'];
                $vendaItem->qtde = $produtoWebStorm->quantidade->__toString();
                $vendaItem->subtotal = bcmul($vendaItem->precoVenda, $vendaItem->qtde, 2);

                $desconto = (float)$produtoWebStorm->desconto->__toString() ?? 0.0;
                $descontof = DecimalUtils::round((float)$produtoWebStorm->descontof->__toString() ?? 0.0, 2, DecimalUtils::ROUND_DOWN);
                $vendaItem->desconto = bcmul(bcadd($desconto, $descontof, 2), $vendaItem->qtde, 2);
                $descontoAcum = (float)bcadd($descontoAcum, $vendaItem->desconto, 2);
                $vendaItem->produto = $produto;

                $vendaItem->jsonData['ecommerce_idItemVenda'] = $produtoWebStorm->idItemVenda->__toString();
                $vendaItem->jsonData['ecommerce_codigo'] = $produtoWebStorm->codigo->__toString();

                $this->vendaItemEntityHandler->save($vendaItem);
                $i++;
            }
            $venda->recalcularTotais();
            try {
                $conn->delete('ven_venda_pagto', ['venda_id' => $venda->getId()]);
            } catch (\Throwable $e) {
                $erro = 'Erro ao deletar pagtos da venda (id = "' . $venda['id'] . ')';
                $this->syslog->err($erro);
                throw new \RuntimeException($erro);
            }
            /** @var PlanoPagtoRepository $repoPlanoPagto */
            $repoPlanoPagto = $this->vendaEntityHandler->getDoctrine()->getRepository(PlanoPagto::class);
            $arrayByCodigo = $repoPlanoPagto->arrayByCodigo();//codigo | descricao
            //-------+------------------------
            //999    | NÃO INFORMADO
            //001    | A VISTA (ESPÉCIE)
            //002    | A VISTA (CHEQUE)
            //003    | A VISTA (CARTÃO DÉBITO)
            //020    | CARTÃO DE CRÉDITO
            //030    | BOLETO
            $tipoFormaPagamento = $pedido->pagamentos->pagamento->tipoFormaPagamento->__toString();
            $vendaPagto = [
                'venda_id' => $venda->getId(),
                'valor_pagto' => $venda->valorTotal,
                'json_data' => [
                    'idFormaPagamento' => $pedido->pagamentos->pagamento->idFormaPagamento->__toString(),
                    'nomeFormaPagamento' => $pedido->pagamentos->pagamento->nomeFormaPagamento->__toString(),
                    'tipoFormaPagamento' => $pedido->pagamentos->pagamento->tipoFormaPagamento->__toString(),
                    'desconto' => $pedido->pagamentos->pagamento->desconto->__toString(),
                    'parcelas' => $pedido->pagamentos->pagamento->parcelas->__toString(),
                    'valorParcela' => $pedido->pagamentos->pagamento->valorParcela->__toString(),
                ],
                'inserted' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated' => (new \DateTime())->format('Y-m-d H:i:s'),
                'version' => 0,
                'user_inserted_id' => 1,
                'user_updated_id' => 1,
                'estabelecimento_id' => 1
            ];
            $descricaoPlanoPagto = null;
            switch ($tipoFormaPagamento) {
                case 'boleto':
                    $vendaPagto['plano_pagto_id'] = $arrayByCodigo['030']['id'];
                    $descricaoPlanoPagto = $arrayByCodigo['030']['descricao'];
                    break;
                case 'cartao':
                    $vendaPagto['plano_pagto_id'] = $arrayByCodigo['020']['id'];
                    $descricaoPlanoPagto = $arrayByCodigo['020']['descricao'];
                    $vendaPagto['json_data']['nsu'] = $pedido->pagamentos->pagamento->NSU->__toString();
                    $vendaPagto['json_data']['tid'] = $pedido->pagamentos->pagamento->TID->__toString();
                    break;
                default:
                    $vendaPagto['plano_pagto_id'] = $arrayByCodigo['999']['id'];
                    $descricaoPlanoPagto = $arrayByCodigo['999']['descricao'];
                    break;
            }
            $vendaPagto['json_data'] = json_encode($vendaPagto['json_data']);
            try {
                $conn->insert('ven_venda_pagto', $vendaPagto);
            } catch (\Throwable $e) {
                throw new ViewException('Erro ao salvar dados do pagamento');
            }
            $venda->jsonData['infoPagtos'] = $descricaoPlanoPagto .
                ': R$ ' . number_format($venda->valorTotal, 2, ',', '.') . ' - ' .
                $pedido->pagamentos->pagamento->nomeFormaPagamento->__toString() . ' ' .
                ((int)$pedido->pagamentos->pagamento->parcelas->__toString() > 0 ? $pedido->pagamentos->pagamento->parcelas->__toString() . ' parcela(s)' : '');
            $this->vendaEntityHandler->save($venda);
            $conn->commit();
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                try {
                    $conn->rollBack();
                } catch (Exception $e) {
                    throw new ViewException("Erro ao efetuar o rollback - integrarVendaParaCrosier", 0, $e);
                }
            }
            $this->syslog->err('Erro ao integrarVendaParaCrosier', $pedido->asXML());
            throw new ViewException('Erro ao integrarVendaParaCrosier', 0, $e);
        }
    }

    /**
     * @param Venda $venda
     * @return void
     * @throws ViewException
     */
    public function reintegrarVendaParaCrosier(Venda $venda): void
    {
        if (!($venda->jsonData['ecommerce_idPedido'] ?? false)) {
            throw new ViewException('Venda sem ecommerce_idPedido');
        }
        $xml = '<![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>
                    <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <chave>' . $this->getChave() . '</chave>
                    <acao>select</acao>
                    <modulo>pedido</modulo>    
                    <filtro>
                        <idPedido>' . (int)$venda->jsonData['ecommerce_idPedido'] . '</idPedido>
                        <idCliente></idCliente>
                        <cpf_cnpj></cpf_cnpj>
                        <dataInicial></dataInicial>
                        <dataFinal></dataFinal>
                        <status></status>
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

        $pedidoXml = $xmlResult->resultado->filtro->pedido ?? null;

        if (!$pedidoXml) {
            throw new ViewException('Pedido não encontrado no e-commerce');
        }

        $this->integrarVendaParaCrosier($pedidoXml, true);
    }


    /**
     * @param Venda $venda
     * @return \SimpleXMLElement|null
     * @throws ViewException
     */
    public function integrarVendaParaEcommerce(Venda $venda)
    {

        try {
            $conn = $this->vendaEntityHandler->getDoctrine()->getConnection();
            $rsVenda = $conn->fetchAllAssociative('SELECT nf.numero, nf.serie, nf.chave_acesso FROM fis_nf nf, fis_nf_venda nfvenda WHERE nf.id = nfvenda.nota_fiscal_id AND nf.cstat = 100 AND nfvenda.venda_id = :vendaId',
                ['vendaId' => $venda->getId()]);
            $dadosNota = '';
            if ($rsVenda[0]['numero'] ?? false) {
                if (count($rsVenda) > 1) {
                    throw new ViewException('Mais de uma nota fiscal encontrada para esta venda. Verifique.');
                }
                $dadosNota = '
                <notaFiscal>
                        <numero>' . $rsVenda[0]['numero'] . '</numero>
                        <serie>' . $rsVenda[0]['serie'] . '</serie>
                        <chave>' . $rsVenda[0]['chave_acesso'] . '</chave>
                    </notaFiscal>';
            }
            $xml = '<![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <chave>' . $this->getChave() . '</chave>
                <acao>update</acao>
                <modulo>alterarStatusPedido</modulo>    
                <statusPedido>
                    <idPedido>' . $venda->jsonData['ecommerce_idPedido'] . '</idPedido>
                    <status>' . $venda->jsonData['ecommerce_status'] . '</status>' .
                $dadosNota .
                '</statusPedido>
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
        } catch (\Throwable $e) {
            $errMsg = 'integrarVendaParaEcommerce() - ERRO (venda.id = "' . $venda->getId() . '")';
            if ($e instanceof ViewException) {
                $errMsg .= ' - ' . $e->getMessage();
            }
            $this->syslog->err($errMsg, $e->getTraceAsString());
            throw new ViewException($errMsg);
        }
    }

    /**
     * @return \nusoap_client
     */
    private function getNusoapClientExportacaoInstance(): \nusoap_client
    {
        if (!isset($this->nusoapClientExportacao)) {

            $endpoint = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class)
                ->findValorByChaveAndAppUUID('ecomm_info_integra_WEBSTORM_endpoint_export', $_SERVER['CROSIERAPPRADX_UUID']);
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
                ->findValorByChaveAndAppUUID('ecomm_info_integra_WEBSTORM_endpoint_import', $_SERVER['CROSIERAPPRADX_UUID']);
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


    private function corrigirVinculosCrosierWebStorm(Produto $produto, int $ecommerceId)
    {
        $client = new Client();

        $uuid = StringUtils::guidv4();

        $headers = [
            'authority' => 'www.rodoponta.com.br',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'accept-language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'cookie' => 'PHPSESSID=' . $uuid,
            'x-requested-with' => 'XMLHttpRequest',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/81.0.4044.138 Chrome/81.0.4044.138 Safari/537.36',
            'origin' => 'https://ww3.finersistemas.com',
            'sec-fetch-site' => 'same-origin',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-dest' => 'document',
            'sec-fetch-user' => '?1',
            'sec-gpc' => '1',
            'upgrade-insecure-requests' => '1',
            'referer' => 'https://www.rodoponta.com.br/admin',
        ];

        $headers['content-type'] = 'application/x-www-form-urlencoded';

        // login
        $rs = $client->request('POST',
            'https://www.rodoponta.com.br/admin/autenticacao/login',
            [
                'headers' => $headers,
                'body' => 'usuario=' . $_SERVER['webstorm_username'] . '&senha=' . $_SERVER['webstorm_password'] . '%40',
            ]
        );

        $resultado = utf8_decode($rs->getBody()->getContents());

        if ($resultado !== '<meta http-equiv="X-UA-Compatible" content="IE=7"><meta HTTP-EQUIV = \'Refresh\' CONTENT = \'0; URL = https://www.rodoponta.com.br/admin/principal\'>') {
            throw new ViewException('Não foi possível efetuar o login na WebStorm');
        }

        unset($headers['content-type']);

        // login
        $rs = $client->request('GET',
            'https://www.rodoponta.com.br/admin/modulo/produto/incluir/0/' . $ecommerceId,
            [
                'headers' => $headers,
            ]
        );

        $resultado = utf8_decode($rs->getBody()->getContents());

        $ecommerceItemVendaId = (int)substr($resultado, strpos($resultado, 'onclick="editaFormPasso') + 27, 5);
        $produto->jsonData['ecommerce_id'] = $ecommerceId;
        $produto->jsonData['ecommerce_item_venda_id'] = $ecommerceItemVendaId;
        $this->produtoEntityHandler->gerarThumbnailAoSalvar = false;
        $this->produtoEntityHandler->save($produto);
    }

}
