<?php

namespace App\Repository\Fiscal;

use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalVenda;
use App\Entity\Vendas\Venda;
use App\Utils\Fiscal\NFeUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Repository para a entidade NotaFiscalVenda.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class NotaFiscalVendaRepository extends ServiceEntityRepository
{


    /**
     * @var NFeUtils
     */
    private $nfeUtils;

    public function __construct(ManagerRegistry $registry, NFeUtils $nfeUtils)
    {
        parent::__construct($registry, NotaFiscalVenda::class);
        $this->nfeUtils = $nfeUtils;
    }

    /**
     * @param Venda $venda
     * @return null|NotaFiscalVenda
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function findNotaFiscalByVenda(Venda $venda): ?NotaFiscal
    {
        $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();

        $ambiente = $nfeConfigs['tpAmb'] === 1 ? 'PROD' : 'HOM';

        $ql = "SELECT nf FROM App\Entity\Fiscal\NotaFiscalVenda nfv JOIN App\Entity\Fiscal\NotaFiscal nf WHERE nfv.notaFiscal = nf AND nfv.venda = :venda AND nf.ambiente = :ambiente";
        $query = $this->getEntityManager()->createQuery($ql);
        $query->setParameters([
            'venda' => $venda,
            'ambiente' => $ambiente
        ]);

        $results = $query->getResult();

        if (count($results) > 1) {
            throw new \LogicException('Mais de uma Nota Fiscal encontrada para [' . $venda->getId() . ']');
        }

        return count($results) === 1 ? $results[0] : null;
    }
}
