<?php


namespace App\Business\ECommerce;

use App\Entity\Estoque\Depto;
use App\Entity\Estoque\Grupo;
use App\Entity\Estoque\Subgrupo;
use App\EntityHandler\Estoque\DeptoEntityHandler;
use App\EntityHandler\Estoque\GrupoEntityHandler;
use App\EntityHandler\Estoque\SubgrupoEntityHandler;
use App\Repository\Estoque\DeptoRepository;
use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use Symfony\Component\Security\Core\Security;

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

    public function __construct(AppConfigEntityHandler $appConfigEntityHandler, Security $security,
                                DeptoEntityHandler $deptoEntityHandler, GrupoEntityHandler $grupoEntityHandler, SubgrupoEntityHandler $subgrupoEntityHandler)
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
            throw new \RuntimeException('Erro ao instanciar IntegraWebStorm');
        }
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->grupoEntityHandler = $grupoEntityHandler;
        $this->subgrupoEntityHandler = $subgrupoEntityHandler;
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

            $marcasNaBase = $this->appConfigEntityHandler->getDoctrine()->getConnection()->fetchAll('SELECT distinct(trim(json_data->>"$.marca")) as marca FROM est_produto WHERE trim(json_data->>"$.marca") != \'\'');

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
                        'webstorm_id' => $idIntegr,
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

        $xml = '<![CDATA[<?xml version="1.0" encoding="UTF-8"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>insert</acao>
               <modulo>marca</modulo>
               <marca pk="idMarca">
                  <idMarca></idMarca>
                  <nome>' . $marca . '</nome>
               </marca>
            </ws_integracao>]]>';

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $arResultado = $client->call('marcaAdd', [
            'xml' => utf8_encode($xml)
        ]);

        $xmlResult = simplexml_load_string($arResultado);

        return (int)$xmlResult->idMarca->__toString();
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

            if (!isset($depto->jsonData['webstorm_id'])) {
                $idNivelPai_depto = $this->integraDeptoGrupoSubgrupo($depto->nome, 1);
                $depto->jsonData = [
                    'webstorm_id' => $idNivelPai_depto,
                    'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'integrado_por' => $this->security->getUser()->getUsername()
                ];

                $this->deptoEntityHandler->save($depto);
            } else {
                $idNivelPai_depto = $depto->jsonData['webstorm_id'];
                $this->integraDeptoGrupoSubgrupo($depto->nome, 1, null, $idNivelPai_depto);
                $depto->jsonData = [
                    'webstorm_id' => $idNivelPai_depto,
                    'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'integrado_por' => $this->security->getUser()->getUsername()
                ];
                $this->deptoEntityHandler->save($depto);
            }


            /** @var Grupo $grupo */
            foreach ($depto->grupos as $grupo) {
                if (!isset($grupo->jsonData['webstorm_id'])) {
                    $idNivelPai_grupo = $this->integraDeptoGrupoSubgrupo($grupo->nome, 2, $idNivelPai_depto);
                    $grupo->jsonData = [
                        'webstorm_id' => $idNivelPai_grupo,
                        'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                        'integrado_por' => $this->security->getUser()->getUsername()
                    ];
                    $this->grupoEntityHandler->save($grupo);
                } else {
                    $idNivelPai_grupo = $grupo->jsonData['webstorm_id'];
                    $this->integraDeptoGrupoSubgrupo($grupo->nome, 2, $idNivelPai_depto, null, $idNivelPai_grupo);
                    $grupo->jsonData = [
                        'webstorm_id' => $idNivelPai_grupo,
                        'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                        'integrado_por' => $this->security->getUser()->getUsername()
                    ];
                    $this->grupoEntityHandler->save($grupo);
                }


                /** @var Subgrupo $subgrupo */
                foreach ($grupo->subgrupos as $subgrupo) {
                    if (!isset($subgrupo->jsonData['webstorm_id'])) {
                        $webstorm_id = $this->integraDeptoGrupoSubgrupo($subgrupo->nome, 3, $idNivelPai_depto, $idNivelPai_grupo);
                        $subgrupo->jsonData = [
                            'webstorm_id' => $webstorm_id,
                            'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'integrado_por' => $this->security->getUser()->getUsername()
                        ];
                        $this->subgrupoEntityHandler->save($subgrupo);
                    } else {
                        $webstorm_id = $subgrupo->jsonData['webstorm_id'];
                        $this->integraDeptoGrupoSubgrupo($subgrupo->nome, 3, $idNivelPai_depto, $idNivelPai_grupo, $webstorm_id);
                        $subgrupo->jsonData = [
                            'webstorm_id' => $webstorm_id,
                            'integrado_em' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'integrado_por' => $this->security->getUser()->getUsername()
                        ];
                        $this->subgrupoEntityHandler->save($subgrupo);
                    }
                }


            }


        }
    }

    /**
     * Integra um Depto, Grupo ou Subgrupo.
     *
     * @param string $descricao
     * @param int $nivel
     * @param int|null $idNivelPai1
     * @param int|null $idNivelPai2
     * @param int|null $webstorm_id
     * @return int
     */
    private function integraDeptoGrupoSubgrupo(string $descricao, int $nivel, ?int $idNivelPai1 = null, ?int $idNivelPai2 = null, ?int $webstorm_id = null)
    {
        $client = $this->getNusoapClientImportacaoInstance();

        $pais = '';
        if ($nivel === 2) {
            $pais = '<idDepartamentoNivel1>' . $idNivelPai1 . '</idDepartamentoNivel1>';
        } elseif ($nivel === 3) {
            $pais = '<idDepartamentoNivel1>' . $idNivelPai1 . '</idDepartamentoNivel1>';
            $pais .= '<idDepartamentoNivel2>' . $idNivelPai2 . '</idDepartamentoNivel2>';
        }

        $xml = '<![CDATA[<?xml version="1.0" encoding="UTF-8"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $this->chave . '</chave>
               <acao>' . ($webstorm_id ? 'update' : 'insert') . '</acao>
               <modulo>departamento</modulo>
               <marca pk="idDepartamento">
                  <idDepartamento>' . $webstorm_id . '</idDepartamento>
                  <nome>' . $descricao . '</nome>
                  <nivel>' . $nivel . '</nivel>' . $pais . '
               </marca>
            </ws_integracao>]]>';

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }

        $arResultado = $client->call('departamento' . ($webstorm_id ? 'Update' : 'Add') , [
            'xml' => utf8_encode($xml)
        ]);

        $xmlResult = simplexml_load_string($arResultado);


        return (int)$xmlResult->idDepartamento->__toString();
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
            $client->soap_defencoding = 'UTF-8';
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
            $client->soap_defencoding = 'UTF-8';
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