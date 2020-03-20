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
use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @param string $marca
     * @return int
     * @throws ViewException
     */
    public function verificaOuIntegraMarca(string $marca): int
    {
        /** @var AppConfig $appConfigMarcas */
        $appConfigMarcas = $this->repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_marcas'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
        if (!$appConfigMarcas) {
            throw new \LogicException('ecomm_info_integra_marcas N/D');
        }

        $json = json_decode($appConfigMarcas->getValor(), true);
        /** @var array $marca */
        foreach ($json['marcas'] as $marcaCadastrada) {
            if ($marcaCadastrada['nome_no_crosier'] === $marca && $marcaCadastrada['ecommerce_id']) {
                return $marcaCadastrada['ecommerce_id'];
            }
        }
        // se chegou aqui, é porque ainda não existe
        return $this->integraMarca($marca);
    }

    /**
     * Integra as marcas que ainda não tenham sido integradas.
     *
     *
     * @throws ViewException
     */
    public function integrarMarcas(): void
    {
        try {
            /** @var AppConfig $appConfigMarcas */
            $appConfigMarcas = $this->repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_marcas'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfigMarcas) {
                throw new \LogicException('ecomm_info_integra_marcas N/D');
            }

            $json = json_decode($appConfigMarcas->getValor(), true);

            $marcasNaBase = $this->appConfigEntityHandler
                ->getDoctrine()->getConnection()->fetchAll('SELECT distinct(trim(json_data->>"$.marca")) as marca FROM est_produto WHERE trim(IFNULL(json_data->>"$.marca",\'\')) NOT IN (\'\',\'null\')');

            $jsonMarcas = [];
            foreach ($json['marcas'] as $marcaNoJson) {
                $jsonMarcas[] = $marcaNoJson['nome_no_crosier'];
            }

            $now = (new \DateTime())->format('Y-m-d H:i:s');

            $mudou = false;

            foreach ($marcasNaBase as $marcaNaBase) {
                if (!in_array($marcaNaBase['marca'], $jsonMarcas)) {
                    $idIntegr = $this->integraMarca($marcaNaBase['marca']);
                    $json['marcas'][] = [
                        'nome_no_crosier' => $marcaNaBase['marca'],
                        'ecommerce_id' => $idIntegr,
                        'integrado_em' => $now
                    ];
                    $mudou = true;
                }
            }

            if ($mudou) {
                $json['ultima_integracao'] = (new \DateTime())->format('Y-m-d H:i:s');
                $json['integrado_por'] = $this->security->getUser()->getUsername();
                $appConfigMarcas->setValor(json_encode($json));
                $this->appConfigEntityHandler->save($appConfigMarcas);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao marcar app_config (estoque.dthrAtualizacao)');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro - integrarMarcas()');
        }
    }

    /**
     * @param string $marca
     * @return int
     */
    private function integraMarca(string $marca): int
    {

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
            'xml' => utf8_encode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $xmlResult = simplexml_load_string($arResultado);

        return (int)$xmlResult->idMarca->__toString();
    }


    /**
     * @param string $campo
     * @param string $tipoCaracteristica
     * @return int
     * @throws ViewException
     */
    private function integraTipoCaracteristica(string $campo, string $tipoCaracteristica): int
    {

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
            'xml' => utf8_encode($xml)
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

        /** @var AppConfig $appConfig */
        $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
        $jsonMetadata = json_decode($appConfig->getValor(), true);
        $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['ecommerce_id'] = (int)$xmlResult->idTipoCaracteristica->__toString();
        $appConfig->setValor(json_encode($jsonMetadata));
        $this->appConfigEntityHandler->save($appConfig);

        return $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['ecommerce_id'];
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
            'xml' => utf8_encode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $xmlResult = simplexml_load_string($arResultado);

        /** @var AppConfig $appConfig */
        $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
        $jsonMetadata = json_decode($appConfig->getValor(), true);
        $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['sugestoes_ids'][$caracteristica] = (int)$xmlResult->caracteristicas->caracteristica->idCaracteristica->__toString();
        $appConfig->setValor(json_encode($jsonMetadata));
        $this->appConfigEntityHandler->save($appConfig);

        return $jsonMetadata['campos'][$campo]['info_integr_ecommerce']['sugestoes_ids'][$caracteristica];
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
     * @param Depto $depto
     * @return Depto
     * @throws ViewException
     */
    public function integraDepto(Depto $depto): Depto
    {
        if (!isset($depto->jsonData['ecommerce_id'])) {
            $idNivelPai_depto = $this->integraDeptoGrupoSubgrupo($depto->nome, 1);
            $depto->jsonData = [
                'ecommerce_id' => $idNivelPai_depto,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
        } else {
            $idNivelPai_depto = $depto->jsonData['ecommerce_id'];
            $this->integraDeptoGrupoSubgrupo($depto->nome, 1, null, null, $idNivelPai_depto);
            $depto->jsonData = [
                'ecommerce_id' => $idNivelPai_depto,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
        }
        /** @var Depto $rDepto */
        $rDepto = $this->deptoEntityHandler->save($depto);
        return $rDepto;
    }

    /**
     * @param Grupo $grupo
     * @return Grupo
     * @throws ViewException
     */
    public function integraGrupo(Grupo $grupo): Grupo
    {
        $idNivelPai_depto = $grupo->depto->jsonData['ecommerce_id'];
        if (!isset($grupo->jsonData['ecommerce_id'])) {
            $idNivelPai_grupo = $this->integraDeptoGrupoSubgrupo($grupo->nome, 2, $idNivelPai_depto);
            $grupo->jsonData = [
                'ecommerce_id' => $idNivelPai_grupo,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
        } else {
            $idNivelPai_grupo = $grupo->jsonData['ecommerce_id'];
            $this->integraDeptoGrupoSubgrupo($grupo->nome, 2, $idNivelPai_depto, null, $idNivelPai_grupo);
            $grupo->jsonData = [
                'ecommerce_id' => $idNivelPai_grupo,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];

        }
        /** @var Grupo $rGrupo */
        $rGrupo = $this->grupoEntityHandler->save($grupo);
        return $rGrupo;
    }

    /**
     * @param Subgrupo $subgrupo
     * @return Subgrupo
     * @throws ViewException
     */
    public function integraSubgrupo(Subgrupo $subgrupo): Subgrupo
    {
        $idNivelPai_depto = $subgrupo->grupo->depto->jsonData['ecommerce_id'];
        $idNivelPai_grupo = $subgrupo->grupo->jsonData['ecommerce_id'];
        if (!isset($subgrupo->jsonData['ecommerce_id'])) {
            $ecommerce_id = $this->integraDeptoGrupoSubgrupo($subgrupo->nome, 3, $idNivelPai_depto, $idNivelPai_grupo);
            $subgrupo->jsonData = [
                'ecommerce_id' => $ecommerce_id,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
        } else {
            $ecommerce_id = $subgrupo->jsonData['ecommerce_id'];
            $this->integraDeptoGrupoSubgrupo($subgrupo->nome, 3, $idNivelPai_depto, $idNivelPai_grupo, $ecommerce_id);
            $subgrupo->jsonData = [
                'ecommerce_id' => $ecommerce_id,
                'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                'integrado_por' => $this->security->getUser()->getUsername()
            ];
        }
        /** @var Subgrupo $rSubgrupo */
        $rSubgrupo = $this->subgrupoEntityHandler->save($subgrupo);
        return $rSubgrupo;
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
            'xml' => utf8_encode($xml)
        ]);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $xmlResult = simplexml_load_string($arResultado);


        return (int)$xmlResult->idDepartamento->__toString();
    }


    /**
     * @param Produto $produto
     * @return int
     * @throws ViewException
     */
    public function integraProduto(Produto $produto)
    {
        // Verifica se o depto, grupo e subgrupo já estão integrados
        $idDepto = $produto->depto->jsonData['ecommerce_id'] ?? $this->integraDepto($produto->depto)->getId();
        $idGrupo = $produto->grupo->jsonData['ecommerce_id'] ?? $this->integraGrupo($produto->grupo)->getId();
        $idSubgrupo = $produto->subgrupo->jsonData['ecommerce_id'] ?? $this->integraSubgrupo($produto->subgrupo)->getId();

        $idMarca = null;
        if ($produto->jsonData['marca'] ?? false) {
            $idMarca = $this->verificaOuIntegraMarca($produto->jsonData['marca']);
        }

        $dimensoes = explode('|', $produto->jsonData['dimensoes']);

        $altura = $dimensoes[0] ?? 0.0;
        $largura = $dimensoes[1] ?? 0.0;
        $comprimento = $dimensoes[2] ?? 0.0;

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
            '<descricao-especificacoes-tecnicas>' . htmlspecialchars($produto->jsonData['especif-tec'] ?? '') . '</descricao-especificacoes-tecnicas>';

        /** @var AppConfig $appConfig */
        $appConfig = $this->repoAppConfig->findAppConfigByChave('est_produto_json_metadata');
        if (!$appConfig) {
            throw new \LogicException('ecomm_info_integra_marcas N / D');
        }

        $jsonCampos = json_decode($appConfig->getValor(), true)['campos'];

        foreach ($produto->jsonData as $campo => $valor) {
            if (isset($jsonCampos[$campo]['info_integr_ecommerce']['tipo_campo_ecommerce']) && $jsonCampos[$campo]['info_integr_ecommerce']['tipo_campo_ecommerce'] === 'caracteristica') {
                $ecommerceId_tipoCaracteristica = $jsonCampos[$campo]['info_integr_ecommerce']['ecommerce_id'] ?? $this->integraTipoCaracteristica($campo, $jsonCampos[$campo]['label']);
                if ($jsonCampos[$campo]['tipo'] === 'tags') {
                    $valoresTags = explode(',', $valor);
                    foreach ($valoresTags as $valorTag) {
                        $ecommerceId_caracteristica = $jsonCampos[$campo]['info_integr_ecommerce']['sugestoes_ids'][$valorTag] ?? $this->integraCaracteristica($campo, $ecommerceId_tipoCaracteristica, $valorTag);
                        $xml .= '<caracteristicaProduto><idCaracteristica>' . $ecommerceId_caracteristica . '</idCaracteristica></caracteristicaProduto>';
                    }
                } else {
                    $ecommerceId_caracteristica = $jsonCampos[$campo]['info_integr_ecommerce']['sugestoes_ids'][$valor] ?? $this->integraCaracteristica($campo, $ecommerceId_tipoCaracteristica, $valor);
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
				<peso>' . $produto->jsonData['peso'] . '</peso>
				<altura>' . $altura . '</altura>
				<largura>' . $largura . '</largura>
				<comprimento>' . $comprimento . '</comprimento>
            </itensVenda></produto>' .
            '</ws_integracao>]]>';

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

        $xmlResult = simplexml_load_string($arResultado);

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
            $client = new \nusoap_client($endpoint, 'wsdl');
            $client->setEndpoint($endpoint);
            $client->soap_defencoding = 'UTF - 8';
            $client->decode_utf8 = false;
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
            $client = new \nusoap_client($endpoint . '?wsdl', 'wsdl');
            $client->setEndpoint('https://rodoponta.webstorm.com.br/webservice/serverImportacao');
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