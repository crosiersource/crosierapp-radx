<?php


namespace App\Business\ECommerce;


/**
 * Class IntegradorBusinessFactory
 * @package App\Business\ECommerce
 */
class IntegradorBusinessFactory
{

    private IntegradorWebStorm $integradorWebStorm;

    /**
     * @required
     * @param IntegradorWebStorm $integradorWebStorm
     */
    public function setIntegraWebStorm(IntegradorWebStorm $integradorWebStorm): void
    {
        $this->integradorWebStorm = $integradorWebStorm;
    }

    /**
     * @param string $integrador
     * @return IntegradorWebStorm
     */
    public function getIntegrador(string $integrador)
    {
        switch ($integrador) {
            case 'WEBSTORM':
                return $this->integradorWebStorm;
                break;
            default:
                throw new \RuntimeException('integrador n/d');
        }
    }


}