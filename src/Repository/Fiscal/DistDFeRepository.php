<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\DistDFe;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;
use Doctrine\DBAL\Connection;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class DistDFeRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return DistDFe::class;
    }

    /**
     *
     * @param string $documento
     * @return int
     */
    public function findPrimeiroNSU(string $documento): int
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        $r = $conn->fetchAll('SELECT min(nsu) as primeiro_nsu FROM fis_distdfe WHERE documento = :documento', ['documento' => $documento]);
        return $r[0]['primeiro_nsu'];
    }

    /**
     *
     * @param string $documento
     * @return int
     */
    public function findUltimoNSU(string $documento): int
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        $r = $conn->fetchAll('SELECT max(nsu) as ultimo_nsu FROM fis_distdfe WHERE documento = :documento', ['documento' => $documento]);
        return $r[0]['ultimo_nsu'] ?? 0;
    }


    /**
     *
     * @param string $documento
     * @return null|array
     */
    public function findAllNSUs(string $documento): ?array
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        return $conn->fetchAll('SELECT nsu FROM fis_distdfe WHERE nsu IS NOT NULL AND documento = :documento ORDER BY nsu', ['documento' => $documento]);
    }


    /**
     * Encontra todos os DistDFes que sejam referentes a Notas Fiscais (e não eventos), que ainda não estejam na fis_nf.
     * @param string $documento
     * @return mixed
     */
    public function findDistDFeNotInNotaFiscal(string $documento)
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        // Pego todos os distdfes que não foram salvos como uma NF ou aqueles que são do tipo RESNFE e que não tem o correspondente NFEPROC sem uma NF vinculada
        // Isso porque o rumo normal é:
        // 1-Ter um RESNFE sem NF.
        // 2-Esse RESNFE gerar uma NF.
        // 3-Manifestar ciência/confirmação do DISTDFE.
        // 4-Baixar a distDFe com o NFEPROC da nota completa.
        // 5-Revincular a fis_nf ao distDFe completo)
        return $conn->fetchAll('SELECT id FROM fis_distdfe 
            WHERE documento = :documento AND 
                  tipo_distdfe IN (\'NFEPROC\',\'RESNFE\') AND 
                  (nota_fiscal_id IS NULL OR 
                    (tipo_distdfe = \'RESNFE\' AND chnfe IN (SELECT chnfe FROM fis_distdfe WHERE tipo_distdfe = \'NFEPROC\' AND nota_fiscal_id IS NULL)))', ['documento' => $documento]);
    }

    /**
     * Encontra todos os DistDFes que sejam referentes a eventos, que ainda não estejam na fis_nf_evento
     * @param string $documento
     * @return mixed
     */
    public function findDistDFeNotInNotaFiscalEvento(string $documento)
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        return $conn->fetchAll('SELECT id FROM fis_distdfe WHERE tipo_distdfe IN(\'PROCEVENTONFE\',\'RESEVENTO\') AND 
                                 (chnfe,tp_evento,nseq_evento) NOT IN (SELECT nf.chave_acesso, evento.tp_evento, evento.nseq_evento FROM fis_nf nf, fis_nf_evento evento WHERE evento.nota_fiscal_id = nf.id) 
                                 AND chnfe IN (SELECT chave_acesso FROM fis_nf)', ['documento' => $documento]);
    }

}
