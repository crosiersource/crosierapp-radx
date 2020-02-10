<?php


namespace App\Business\ECommerce;

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

    private \nusoap_client $nusoapClientExportacao;

    private \nusoap_client $nusoapClientImportacao;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private Security $security;

    /**
     * @required
     * @param AppConfigEntityHandler $appConfigEntityHandler
     */
    public function setAppConfigEntityHandler(AppConfigEntityHandler $appConfigEntityHandler): void
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }

    /**
     * @required
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

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
                'SELECT distinct(valor) FROM est_produto_atributo WHERE valor IS NOT NULL and trim(valor) != \'\' AND atributo_id = (SELECT id FROM est_atributo WHERE uuid = ?)',
                [$uuidAtributoMarca]
            );

            $jsonMarcas = [];
            foreach ($json['marcas'] as $marcaNoJson) {
                $jsonMarcas[] = $marcaNoJson['nome_no_crosier'];
            }

            $now = (new \DateTime())->format('Y-m-d H:i:s');

            $mudou = false;

            foreach ($marcasNaBase as $marcaNaBase) {
                if (!in_array($marcaNaBase['valor'], $jsonMarcas)) {
                    $idIntegr = $this->integraMarca($marcaNaBase['valor'], $chave);
                    $json['marcas'][] = [
                        'nome_no_crosier' => $marcaNaBase['valor'],
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
    private function integraMarca(string $marca, string $chave): int
    {

        $client = $this->getNusoapClientImportacaoInstance();

        $xml = '<![CDATA[<?xml version="1.0" encoding="UTF-8"?>
            <ws_integracao xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
               <chave>' . $chave . '</chave>
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
            'xml' => $xml
        ]);

        $xmlResult = simplexml_load_string($arResultado);

        return (int)$xmlResult->idMarca->__toString();
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