<?php

namespace App\EntityHandler\Estoque;

use App\Entity\Estoque\Atributo;
use App\Repository\Estoque\AtributoRepository;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;

/**
 *
 * @author Carlos Eduardo Pauluk
 */
class AtributoEntityHandler extends EntityHandler
{

    public function getEntityClass(): string
    {
        return Atributo::class;
    }

    public function beforeSave(/** @var Atributo $atributo */ $atributo)
    {
        /** @var AtributoRepository $repoAtributo */
        $repoAtributo = $this->getDoctrine()->getRepository(Atributo::class);

        if (!$atributo->getUUID()) {
            $atributo->setUUID(StringUtils::guidv4());
        }
        if (!$atributo->getOrdem()) {
            if (!$atributo->getPaiUUID()) {
                $atributo->setOrdem(1);
            } else {

                /** @var Atributo $ultimaSubcateg */
                $ultimaSubcateg = $repoAtributo->findOneBy(['paiUUID' => $atributo->getPaiUUID()], ['ordem' => 'DESC']);
                $atributo->setOrdem($ultimaSubcateg ? $ultimaSubcateg->getOrdem() + 1 : 1);
            }
        }
        if ($atributo->getPaiUUID()) {
            $atributo->setPrimaria('N');
        }

        $subatributo = $repoAtributo->findBy(['paiUUID' => $atributo->getUUID()], ['ordem' => 'ASC']);
        if ($subatributo) {
            $i = 1;
            /** @var Atributo $subcateg */
            foreach ($subatributo as $subcateg) {
                $subcateg->setOrdem($i++);
                $this->save($subcateg);
            }

        }

    }


}