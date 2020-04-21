<?php

namespace App\Repository\RH;

use App\Entity\RH\Colaborador;
use CrosierSource\CrosierLibBaseBundle\Entity\Config\AppConfig;
use CrosierSource\CrosierLibBaseBundle\Repository\Config\AppConfigRepository;
use CrosierSource\CrosierLibBaseBundle\Repository\FilterRepository;

/**
 * Repository para a entidade Funcionario.
 *
 * @author Carlos Eduardo Pauluk
 *
 */
class ColaboradorRepository extends FilterRepository
{

    public function getEntityClass(): string
    {
        return Colaborador::class;
    }


    public function getJsonMetadata()
    {
        /** @var AppConfigRepository $repoAppConfig */
        $repoAppConfig = $this->getEntityManager()->getRepository(AppConfig::class);
        return $repoAppConfig->findOneBy(
            [
                'appUUID' => $_SERVER['CROSIERAPP_UUID'],
                'chave' => 'rh_colaborador_json_metadata'
            ]
        )->getValor();
    }


}
