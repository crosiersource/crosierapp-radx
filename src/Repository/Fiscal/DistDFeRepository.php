<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\DistDFe;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Psr\Log\LoggerInterface;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class DistDFeRepository extends FilterRepository
{


    /** @var LoggerInterface */
    private $logger;

    /**
     * @required
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }


    public function getEntityClass(): string
    {
        return DistDFe::class;
    }


    /**
     *
     * @return int
     */
    public function findPrimeiroNSU(): int
    {
        try {
            $sql = 'SELECT min(nsu) as primeiro_nsu FROM fis_distdfe';
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
            $sql = 'SELECT max(nsu) as ultimo_nsu FROM fis_distdfe';
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

        $sql = 'SELECT nsu FROM fis_distdfe WHERE nsu IS NOT NULL ORDER BY nsu';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('nsu', 'nsu');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $result = $query->getResult();
        $ret = [];
        foreach ($result as $r) {
            $ret[] = (int)$r['nsu'];
        }
        return $ret;
    }


    /**
     * Encontra todos os DistDFes que sejam referentes a Notas Fiscais (e não eventos), que ainda não estejam na fis_nf.
     * @return mixed
     */
    public function findDistDFeNotInNotaFiscal()
    {
        $sql = 'SELECT id FROM fis_distdfe WHERE tipo_distdfe IN (\'NFEPROC\',\'RESNFE\') AND chnfe NOT IN (SELECT chave_acesso FROM fis_nf WHERE chave_acesso IS NOT NULL AND chave_acesso !=\'\')';
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
     * Encontra todos os DistDFes que sejam referentes a eventos, que ainda não estejam na fis_nf_evento
     * @return mixed
     */
    public function findDistDFeNotInNotaFiscalEvento()
    {
        $sql = 'SELECT id FROM fis_distdfe WHERE tipo_distdfe IN (\'PROCEVENTONFE\',\'RESEVENTO\') AND (chnfe,tp_evento,nseq_evento) NOT IN (SELECT nf.chave_acesso, evento.tp_evento, evento.nseq_evento FROM fis_nf nf, fis_nf_evento evento WHERE evento.nota_fiscal_id = nf.id)';
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

}
