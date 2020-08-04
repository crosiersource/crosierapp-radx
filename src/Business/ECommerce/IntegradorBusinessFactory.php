<?php


namespace App\Business\ECommerce;


use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class IntegradorBusinessFactory
 * @package App\Business\ECommerce
 */
class IntegradorBusinessFactory
{

    private IntegradorWebStorm $integradorWebStorm;

    private EntityManagerInterface $doctrine;

    /**
     * IntegradorBusinessFactory constructor.
     * @param IntegradorWebStorm $integradorWebStorm
     * @param EntityManagerInterface $doctrine
     */
    public function __construct(IntegradorWebStorm $integradorWebStorm, EntityManagerInterface $doctrine)
    {
        $this->integradorWebStorm = $integradorWebStorm;
        $this->doctrine = $doctrine;
    }


    /**
     * @return IntegradorBusiness
     */
    public function getIntegrador()
    {
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $integrador = $repoAppConfig->findByChave('ecomm_info_integra');

        switch ($integrador) {
            case 'WEBSTORM':
                return $this->integradorWebStorm;
                break;
            default:
                throw new \RuntimeException('integrador n/d');
        }
    }


}