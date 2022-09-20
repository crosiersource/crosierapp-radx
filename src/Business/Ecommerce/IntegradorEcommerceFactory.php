<?php


namespace App\Business\Ecommerce;


use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class IntegradorEcommerceFactory implements ServiceSubscriberInterface
{

    private EntityManagerInterface $doctrine;

    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator, EntityManagerInterface $doctrine)
    {
        $this->locator = $locator;
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedServices(): array
    {
        return [
            "WEBSTORM" => IntegradorWebStorm::class,
            "TRAY" => IntegradorTray::class,
            "SIMPLO7" => IntegradorSimplo7::class,
            "MERCADOPAGO" => IntegradorMercadoPago::class,
        ];
    }


    public function getIntegrador(): IntegradorEcommerce
    {
        $repoAppConfig = $this->doctrine->getRepository(AppConfig::class);
        $integrador = $repoAppConfig->findByChave('ecomm_info_integra');

        if ((IntegradorEcommerceFactory::getSubscribedServices()[$integrador] ?? null) &&
            $this->locator->has($integrador)) {
            return $this->locator->get($integrador);
        } else {
            throw new \RuntimeException('integrador n/d');
        }
    }


}
