<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\NotaFiscal;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\AppConfigEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Psr\Log\LoggerInterface;

/**
 * Repository para a entidade NotaFiscal.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalRepository extends FilterRepository
{

    /** @var LoggerInterface */
    private $logger;

    /** @var AppConfigEntityHandler */
    private $appConfigEntityHandler;

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @required
     * @param AppConfigEntityHandler $appConfigEntityHandler
     */
    public function setAppConfigEntityHandler(AppConfigEntityHandler $appConfigEntityHandler): void
    {
        $this->appConfigEntityHandler = $appConfigEntityHandler;
    }


    public function getEntityClass(): string
    {
        return NotaFiscal::class;
    }


    /**
     *
     * @return int
     */
    public function findPrimeiroNSU(): int
    {
        try {
            $sql = 'SELECT min(nsu) as primeiro_nsu FROM fis_nf';
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('primeiro_nsu', 'primeiro_nsu');
            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            return $query->getOneOrNullResult()['primeiro_nsu'] ?? 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     *
     * @return int
     */
    public function findUltimoNSU(): int
    {
        try {
            $sql = 'SELECT max(nsu) as ultimo_nsu FROM fis_nf';
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('ultimo_nsu', 'ultimo_nsu');
            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            return $query->getOneOrNullResult()['ultimo_nsu'] ?? -1;
        } catch (NonUniqueResultException $e) {
            return -1;
        }
    }


    /**
     *
     * @return null|array
     */
    public function findAllNSUs(): ?array
    {
        try {
            $sql = 'SELECT nsu FROM fis_nf WHERE nsu IS NOT NULL ORDER BY nsu';
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('nsu', 'nsu');
            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            $result = $query->getResult();
            $ret = [];
            foreach ($result as $r) {
                $ret[] = intval($r['nsu']);
            }
            return $ret;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }


    /**
     * @param $producao
     * @param $serie
     * @param $tipoNotaFiscal
     * @return int
     * @throws \Exception
     */
    public function findProxNumFiscal(string $ambiente, string $serie, string $tipoNotaFiscal)
    {
        try {

            $this->getEntityManager()->beginTransaction();

            // Ex.: sequenciaNumNF_HOM_NFE_40
            $chave = 'sequenciaNumNF_' . $ambiente . '_' . $tipoNotaFiscal . '_' . $serie;

            $rs = $this->selectAppConfigSequenciaNumNFForUpdate($chave);

            if (!$rs || !$rs[0]) {
                $appConfig = new AppConfig();
                $appConfig->setAppUUID($_SERVER['CROSIERAPP_UUID']);
                $appConfig->setChave($chave);
                $appConfig->setValor(1);
                $this->appConfigEntityHandler->save($appConfig);
                $rs = $this->selectAppConfigSequenciaNumNFForUpdate($chave);
            }
            $prox = $rs[0]['valor'];
            $configId = $rs[0]['id'];

            // Verificação se por algum motivo a numeração na fis_nf já não está pra frente...
            $ultimoNaBase = null;
            $sqlUltimo = "SELECT nf FROM App\Entity\Fiscal\NotaFiscal nf WHERE nf.ambiente = :ambiente AND nf.serie = :serie AND nf.tipoNotaFiscal = :tipoNotaFiscal ORDER BY nf.numero DESC";
            $query = $this->getEntityManager()->createQuery($sqlUltimo);
            $query->setParameters([
                'ambiente' => $ambiente,
                'serie' => $serie,
                'tipoNotaFiscal' => $tipoNotaFiscal
            ]);
            $query->setMaxResults(1);
            $results = $query->getResult();
            if ($results) {
                /** @var NotaFiscal $u */
                $u = $results[0];
                $ultimoNaBase = $u->getNumero();
                if ($ultimoNaBase && $ultimoNaBase !== $prox) {
                    $prox = $ultimoNaBase; // para não pular numeração a toa
                }
            } else {
                $prox = 0;
            }
            $prox++;

            $updateSql = 'UPDATE cfg_app_config SET valor = :valor WHERE id = :id';
            $this->getEntityManager()->getConnection()
                ->executeUpdate($updateSql, ['valor' => $prox, 'id' => $configId]);

            $this->getEntityManager()->commit();

            return $prox;
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            $this->logger->error($e);
            $this->logger->error('Erro ao pesquisar próximo número de nota fiscal para [' . $producao . '] [' . $serie . '] [' . $tipoNotaFiscal . ']');
            throw new \RuntimeException('Erro ao pesquisar próximo número de nota fiscal para [' . $producao . '] [' . $serie . '] [' . $tipoNotaFiscal . ']');
        }
    }

    /**
     * @param string $chave
     * @return mixed
     */
    public function selectAppConfigSequenciaNumNFForUpdate(string $chave)
    {
        // FOR UPDATE para garantir que ninguém vai alterar este valor antes de terminar esta transação
        $sql = 'SELECT id, valor FROM cfg_app_config WHERE app_uuid = :app_uuid AND chave LIKE :chave FOR UPDATE';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('valor', 'valor');
        $rsm->addScalarResult('id', 'id');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('app_uuid', $_SERVER['CROSIERAPP_UUID']);
        $query->setParameter('chave', $chave);
        return $query->getResult();
    }

    public function getDefaultOrders()
    {
        return array(
            'e.id' => 'desc',
            'e.dtEmissao' => 'desc'
        );
    }


    /**
     * Considera (arbitrariamente) como "nota não processada":
     *  - aquelas que tem o XML e a chave de acesso
     *  - mas não tem numero nem data de emissão.
     *  - E a pessoa emitente é diferente do emissor.
     *
     * @return array
     */
    public function findNotasNaoProcessadas(): array
    {
        $sql = 'SELECT id FROM fis_nf WHERE (xml_nota IS NOT NULL) AND (chave_acesso IS NOT NULL) AND (chave_acesso NOT LIKE \'%77498442000134%\')';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $ids = $query->getResult();
        $results = [];
        foreach ($ids as $id) {
            $results[] = $this->find($id);
        }
        return $results;
    }


    /**
     * Considera (arbitrariamente) como "nota não processada":
     *  - aquelas que tem o XML e a chave de acesso
     *  - mas não tem numero nem data de emissão.
     *  - E a pessoa emitente é diferente do emissor.
     *
     * @return array
     */
    public function findNotasComXMLMasSemChave(): array
    {
        $sql = 'SELECT id FROM fis_nf WHERE (xml_nota IS NOT NULL AND trim(xml_nota) != \'\') AND (chave_acesso IS NULL OR trim(chave_acesso) = \'\')';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $ids = $query->getResult();
        $results = [];
        foreach ($ids as $id) {
            $results[] = $this->find($id);
        }
        return $results;
    }


    /**
     * @param string $documento
     * @return array
     */
    public function findUltimosDadosPessoa(string $documento)
    {
        $p = [];
        try {
            $documento = preg_replace("/[^0-9]/", '', $documento);
            /** @var NotaFiscal $ultimo */
            $ultimo = $this->findByFiltersSimpl([['documentoDestinatario', 'EQ', $documento]], ['updated' => 'DESC']);
            $ultimo = $ultimo[0] ?? null;

            if ($ultimo) {
                $p['id'] = $ultimo->getId();
                $p['documento'] = $ultimo->getDocumentoDestinatario();
                $p['nome'] = $ultimo->getXNomeDestinatario();
                $p['ie'] = $ultimo->getInscricaoEstadualDestinatario();
            }
            /** @var NotaFiscal $ultimoComEndereco */
            $ultimoComEndereco = $this->findByFiltersSimpl([['documentoDestinatario', 'EQ', $documento], ['logradouroDestinatario', 'IS_NOT_NULL']], ['updated' => 'DESC']);
            $ultimoComEndereco = $ultimoComEndereco[0] ?? null;
            if ($ultimoComEndereco) {
                $p['logradouro'] = $ultimoComEndereco->getLogradouroDestinatario();
                $p['numero'] = $ultimoComEndereco->getNumeroDestinatario();
                $p['bairro'] = $ultimoComEndereco->getBairroDestinatario();
                $p['cidade'] = $ultimoComEndereco->getCidadeDestinatario();
                $p['estado'] = $ultimoComEndereco->getEstadoDestinatario();
                $p['cep'] = $ultimoComEndereco->getCepDestinatario();
                $p['fone'] = $ultimoComEndereco->getFoneDestinatario();
                $p['email'] = $ultimoComEndereco->getEmailDestinatario();
            } else {
                $p['logradouro'] = '';
                $p['numero'] = '';
                $p['complemento'] = '';
                $p['bairro'] = '';
                $p['cidade'] = '';
                $p['estado'] = '';
                $p['cep'] = '';
                $p['fone'] = '';
                $p['email'] = '';
            }
        } catch (ViewException $e) {
            $this->logger->error('Erro ao findUltimosDadosPessoa()');
        }

        return $p;

    }


}
