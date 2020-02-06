<?php


namespace App\Business\ECommerce;

use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;

/**
 * Regras de negócio para a integração com a WebStorm.
 *
 * Class IntegraWebStorm
 * @package App\Business\ECommerce
 * @author Carlos Eduardo Pauluk
 */
class IntegraWebStorm extends BaseBusiness
{

    /** @var \nusoap_client */
    private $nusoapClientExportacao;

    /** @var \nusoap_client */
    private $nusoapClientImportacao;

    /**
     * @required
     * @var AppConfigEntityHandler
     */
    private AppConfigEntityHandler $appConfigEntityHandler;

//    /**
//     * @required
//     * @param AppConfigEntityHandler $appConfigEntityHandler
//     */
//    public function setAppConfigEntityHandler(AppConfigEntityHandler $appConfigEntityHandler): void
//    {
//        $this->appConfigEntityHandler = $appConfigEntityHandler;
//    }

    /**
     * Integra as marcas que ainda não tenham sido integradas.
     *
     *
     * @param AppConfigEntityHandler $appConfigEntityHandler
     * @throws ViewException
     */
    public function integrarMarcas(): void
    {

        try {
            /** @var AppConfigRepository $repoAppConfig */
            $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);

            /** @var AppConfig $appConfigChave */
            $appConfigChave = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_WEBSTORM_chave'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfigChave) {
                throw new \LogicException('ecomm_info_integra_WEBSTORM_chave N/D');
            }
            $chave = $appConfigChave->getValor();

            /** @var AppConfig $appConfigMarcas */
            $appConfigMarcas = $repoAppConfig->findOneByFiltersSimpl([['chave', 'EQ', 'ecomm_info_integra_marcas'], ['appUUID', 'EQ', $_SERVER['CROSIERAPP_UUID']]]);
            if (!$appConfigMarcas) {
                throw new \LogicException('ecomm_info_integra_marcas N/D');
            }

            $json = json_decode($appConfigMarcas->getValor(), true);

            $uuidAtributoMarca = $json['UUID_atributo_marca'];
            $marcasNaBase = $this->appConfigEntityHandler->getDoctrine()->getConnection()->fetchAll(
                'SELECT distinct(valor) FROM est_produto_atributo WHERE atributo_id = (SELECT id FROM est_atributo WHERE uuid = ?)',
                [$uuidAtributoMarca]
            );

            $now = (new \DateTime())->format('Y-m-d H:i:s');
            foreach ($json['marcas'] as $marcaNoJson) {
                if (!in_array($marcaNoJson['nome_no_crosier'], $marcasNaBase)) {
                    $idIntegr = $this->integraMarca($marcaNoJson['nome_no_crosier'], $chave);
                    $json['marcas'][] = [
                        'nome_no_crosier' => $marcaNoJson['nome_no_crosier'],
                        'webstorm_id' => $idIntegr,
                        'integrado_em' => $now
                    ];
                }
            }

            $appConfigMarcas->setValor(json_encode($json));
            $this->appConfigEntityHandler->save($appConfigMarcas);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao marcar app_config (estoque.dthrAtualizacao)');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao marcar dt/hr atualização');
        }
    }

    /**
     * @param string $marca
     * @return int
     */
    private function integraMarca(string $marca, string $chave): int
    {

        $client = $this->getNusoapClientExportacaoInstance();

        $xml = '<![CDATA[<?xml version="1.0" encoding="UTF-8"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $chave . '</chave>
               <acao>insert</acao>
               <modulo>marca</modulo>
               <marca pk="idMarca">
                  <idMarca></idMarca>
                  <nome>' . $marca . '</nome>
               </marca>
            </ws_integracao>';

        $arResultado = $client->call('marcaAdd', [
            'xml' => $xml
        ], '', '', false, true);

        if ($client->faultcode) {
            throw new \RuntimeException($client->faultcode);
        }
        // else
        if ($client->getError()) {
            throw new \RuntimeException($client->getError());
        }
        // else

    }


    /**
     * @return \nusoap_client
     */
    private function getNusoapClientExportacaoInstance(): \nusoap_client
    {
        if (!$this->nusoapClientExportacao) {

            $endpoint = $this->getDoctrine()->getRepository(AppConfig::class)
                ->findValorByChaveAndAppUUID('ecomm_info_integra_WEBSTORM_endpoint_export', $_SERVER['CROSIERAPP_UUID']);
            if (!$endpoint) {
                throw new \RuntimeException('endpoint não informado');
            }
            $client = new \nusoap_client($endpoint . '?wsdl', 'wsdl');
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
        if (!$this->nusoapClientImportacao) {

            $endpoint = $this->getDoctrine()->getRepository(AppConfig::class)
                ->findValorByChaveAndAppUUID('ecomm_info_integra_WEBSTORM_endpoint_import', $_SERVER['CROSIERAPP_UUID']);
            if (!$endpoint) {
                throw new \RuntimeException('endpoint não informado');
            }
            $client = new \nusoap_client($endpoint . '?wsdl', 'wsdl');
            $client->setEndpoint($endpoint);
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