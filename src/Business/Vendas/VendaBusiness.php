<?php

namespace App\Business\Vendas;

use App\Entity\Vendas\Venda;
use App\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibBaseBundle\APIClient\CrosierEntityIdAPIClient;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class VendaBusiness
 * @package App\Business\Vendas
 *
 * @author Carlos Eduardo Pauluk
 */
class VendaBusiness
{
    /** @var EntityManagerInterface */
    private $doctrine;

    /** @var VendaEntityHandler */
    private $vendaEntityHandler;

    /** @var CrosierEntityIdAPIClient */
    private $crosierEntityIdAPIClient;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(EntityManagerInterface $doctrine,
                                VendaEntityHandler $vendaEntityHandler,
                                CrosierEntityIdAPIClient $crosierEntityIdAPIClient,
                                LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->crosierEntityIdAPIClient = $crosierEntityIdAPIClient;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function checkAcessoPVs(): bool
    {
        $dir = getenv('PASTAARQUIVOSEKTFISCAL');
        $files = scandir($dir, SCANDIR_SORT_NONE);
        return in_array('controle.txt', $files, true) ? true : false;
    }

    /**
     *
     * @param Venda $venda
     * @return Venda
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function finalizarVenda(Venda $venda): Venda
    {
        $venda->setStatus('FINALIZADA');
        $this->doctrine->persist($venda);
        $this->doctrine->flush();
        return $venda;
    }

    /**
     *
     * @param Venda $venda
     * @return Venda
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function recalcularTotais(Venda $venda): Venda
    {
        $bdTotalItens = 0.0;
        foreach ($venda->getItens() as $item) {
            $bdTotalItens += $item->getTotalItem();
        }
        $totalVenda = $bdTotalItens - abs($venda->getDescontoPlano()) - abs($venda->getDescontoEspecial());
        $venda->setSubTotal($bdTotalItens);
        $venda->setValorTotal($totalVenda);

        $this->doctrine->persist($venda);
        $this->doctrine->flush();
        return $venda;
    }

}