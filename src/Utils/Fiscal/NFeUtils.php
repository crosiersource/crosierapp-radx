<?php


namespace App\Utils\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use Doctrine\DBAL\Connection;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Security;

/**
 * @author Carlos Eduardo Pauluk
 */
class NFeUtils
{

    private Connection $conn;

    private LoggerInterface $logger;

    private AppConfigEntityHandler $appConfigEntityHandler;

    private Security $security;


    /**
     * NFeUtils constructor.
     * @param Connection $conn
     * @param LoggerInterface $logger
     * @param AppConfigEntityHandler $appConfigEntityHandler
     * @param Security $security
     */
    public function __construct(Connection $conn, LoggerInterface $logger, AppConfigEntityHandler $appConfigEntityHandler, Security $security)
    {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->appConfigEntityHandler = $appConfigEntityHandler;
        $this->security = $security;
    }


    /**
     * @throws ViewException
     */
    public function clearCaches(): void
    {
        try {
            $cache = new FilesystemAdapter($_SERVER['CROSIERAPP_ID'] . '.cache', 0, $_SERVER['CROSIER_SESSIONS_FOLDER']);
            $cache->delete('nfeTools_configs');
            $cache->delete('nfeConfigs');
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Erro ao limpar nfeTools e nfeConfigs do cachê');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao limpar nfeTools e nfeConfigs do cachê');
        }
    }


    /**
     * @param array $configs
     * @throws ViewException
     */
    public function saveNFeConfigs(array $configs): void
    {
        try {
            $cache = new FilesystemAdapter($_SERVER['CROSIERAPP_ID'] . '.cache', 0, $_SERVER['CROSIER_SESSIONS_FOLDER']);
            $cache->deleteItem('nfeTools_configs');
            $cache->deleteItem('nfeConfigs');
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Erro ao limpar nfeTools e nfeConfigs do cachê');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao limpar nfeTools e nfeConfigs do cachê');
        }

        // Verifica qual nfeConfigs está em uso no momento
        $idNfeConfigsEmUso = $this->getNfeConfigsIdEmUso();

        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
        /** @var AppConfig $appConfig */
        $appConfig = $repoAppConfig->find($idNfeConfigsEmUso);

        $configsSaved = json_decode($appConfig->getValor(), true);
        $configs['certificado'] = $configs['certificado'] ?? $configsSaved['certificado'];
        $configs['certificadoPwd'] = $configs['certificadoPwd'] ?? $configsSaved['certificadoPwd'];
        $configs['atualizacao'] = $configs['atualizacao']->format('Y-m-d H:i:s.u');

        $appConfig->setChave('nfeConfigs_' . $configs['cnpj']);
        $appConfig->setAppUUID($_SERVER['CROSIERAPP_UUID']);
        $appConfig->setValor(json_encode($configs));
        $this->appConfigEntityHandler->save($appConfig);
    }


    /**
     * Retorna o id do cfg_app_config que contém as nfeConfigs setadas como 'em uso' para o usuário logado.
     *
     * @return mixed
     * @throws ViewException
     */
    public function getNfeConfigsIdEmUso(): int
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);

        /** @var AppConfig $appConfig_nfeConfigsIdEmUso */
        $appConfig_nfeConfigsIdEmUso = $repoAppConfig->findOneBy(['appUUID' => $_SERVER['CROSIERAPP_UUID'], 'chave' => 'nfeConfigsIdEmUso_' . $this->security->getUser()->getUsername()]);
        if ($appConfig_nfeConfigsIdEmUso) {
            return $appConfig_nfeConfigsIdEmUso->getValor();
        } else {
            $appConfig_nfeConfigsIdEmUso = $repoAppConfig->findOneBy(['appUUID' => $_SERVER['CROSIERAPP_UUID'], 'chave' => 'nfeConfigsIdEmUso_padrao']);
            $appConfig_nfeConfigsIdEmUso = new AppConfig();
            $appConfig_nfeConfigsIdEmUso->setChave('nfeConfigsIdEmUso_' . $this->security->getUser()->getUsername());
            $appConfig_nfeConfigsIdEmUso->setAppUUID($_SERVER['CROSIERAPP_UUID']);
            $appConfig_nfeConfigsIdEmUso->setValor($appConfig_nfeConfigsIdEmUso->getChave());
            $this->appConfigEntityHandler->save($appConfig_nfeConfigsIdEmUso);
        }
        return (int)$appConfig_nfeConfigsIdEmUso->getChave();
    }


    /**
     * @param int $id
     * @throws ViewException
     */
    public function saveNfeConfigsIdEmUso(int $id): void
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
        /** @var AppConfig $appConfig_nfeConfigsIdEmUso */
        $appConfig_nfeConfigsIdEmUso = $repoAppConfig->findOneBy(['appUUID' => $_SERVER['CROSIERAPP_UUID'], 'chave' => 'nfeConfigsIdEmUso_' . $this->security->getUser()->getUsername()]);
        $appConfig_nfeConfigsIdEmUso->setValor($id);
        $this->appConfigEntityHandler->save($appConfig_nfeConfigsIdEmUso);
    }


    /**
     * Retorna o Tools a partir do nfeConfigs em uso.
     *
     * @return Tools
     * @throws ViewException
     */
    public function getToolsEmUso(): Tools
    {
        try {
            // Verifica qual nfeConfigs está em uso no momento
            $idNfeConfigsEmUso = $this->getNfeConfigsIdEmUso();
            return $this->getTools($idNfeConfigsEmUso);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter tools do cachê');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao obter tools do cachê');
        }
    }


    /**
     * Retorna o Tools a partir do nfeConfigs de um CNPJ específico.
     *
     * @param string $cnpj
     * @return Tools
     * @throws ViewException
     */
    public function getToolsByCNPJ(string $cnpj): Tools
    {
        try {
            $idNfeConfigs = $this->getNFeConfigsByCNPJ($cnpj);
            return $this->getTools($idNfeConfigs['id']);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter tools do cachê');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao obter tools do cachê');
        }
    }

    /**
     * @param int $idNfeConfigs
     * @return Tools
     */
    private function getTools(int $idNfeConfigs): Tools
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->appConfigEntityHandler->getDoctrine()->getRepository(AppConfig::class);
        /** @var AppConfig $appConfig */
        $appConfig = $repoAppConfig->find($idNfeConfigs);

        $configs = json_decode($appConfig->getValor(), true);
        if ($configs['tpAmb'] === 1) {
            $configs['CSC'] = $configs['CSC_prod'];
            $configs['CSCid'] = $configs['CSCid_prod'];
        } else {
            $configs['CSC'] = $configs['CSC_hom'];
            $configs['CSCid'] = $configs['CSCid_hom'];
        }

        $pfx = base64_decode($configs['certificado']);
        $pwd = $configs['certificadoPwd'];
        $certificate = Certificate::readPfx($pfx, $pwd);
        return new Tools(json_encode($configs), $certificate);
    }


    /**
     * Chamada para pegar informações do CNPJ, Razão Social, etc.
     * Não retorna o certificado nem a senha, pois... ?
     *
     * @param string $cnpj
     * @return array
     * @throws ViewException
     */
    public function getNFeConfigsByCNPJ(string $cnpj): array
    {
        try {
            $nfeConfigsJSON = $this->conn->fetchAssoc('SELECT id, valor FROM cfg_app_config WHERE app_uuid = :appUUID AND chave = :chave',
                ['appUUID' => $_SERVER['CROSIERAPP_UUID'], 'chave' => 'nfeConfigs_' . $cnpj]);
            $a = json_decode($nfeConfigsJSON['valor'], true);
            $a['atualizacao'] = isset($a['atualizacao']) ? DateTimeUtils::parseDateStr($a['atualizacao']) : '';
            $a['id'] = $nfeConfigsJSON['id'];
            $a['razaosocial'] = strtoupper($a['razaosocial']);
            $a['enderEmit_xLgr'] = strtoupper($a['enderEmit_xLgr']);
            $a['enderEmit_xBairro'] = strtoupper($a['enderEmit_xBairro']);
            unset($a['certificado'], $a['certificadoPwd']);
            return $a;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao obter nfeConfigs do cachê');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao obter nfeConfigs do cachê');
        }
    }


    /**
     * Chamada para pegar informações do CNPJ, Razão Social, etc.
     * Não retorna o certificado nem a senha.
     *
     * @return array
     * @throws ViewException
     */
    public function getNFeConfigsEmUso(): array
    {
        try {
            $nfeConfigsJSON = $this->conn->fetchAssoc('SELECT id, valor FROM cfg_app_config WHERE id = :id',
                ['id' => $this->getNfeConfigsIdEmUso()]);
            $a = json_decode($nfeConfigsJSON['valor'], true);
            $a['atualizacao'] = isset($a['atualizacao']) ? DateTimeUtils::parseDateStr($a['atualizacao']) : '';
            $a['id'] = $nfeConfigsJSON['id'];
            $a['razaosocial'] = strtoupper($a['razaosocial']);
            $a['enderEmit_xLgr'] = strtoupper($a['enderEmit_xLgr']);
            $a['enderEmit_xBairro'] = strtoupper($a['enderEmit_xBairro']);
            unset($a['certificado'], $a['certificadoPwd']);
            return $a;
        } catch (\Throwable $e) {
            $this->logger->error('Erro ao obter nfeConfigs do cachê');
            $this->logger->error($e->getMessage());
            throw new ViewException('Erro ao obter nfeConfigs do cachê');
        }
    }


}