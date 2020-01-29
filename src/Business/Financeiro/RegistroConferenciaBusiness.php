<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 26/12/18
 * Time: 22:29
 */

namespace App\Business\Financeiro;


use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use App\Entity\Financeiro\RegistroConferencia;
use App\EntityHandler\Financeiro\RegistroConferenciaEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\DateTimeUtils\DateTimeUtils;

class RegistroConferenciaBusiness extends BaseBusiness
{

    private $registroConferenciaEntityHandler;

    /**
     * @param RegistroConferencia $registroConferencia
     * @throws ViewException
     */
    public function gerarProximo(RegistroConferencia $registroConferencia)
    {
        $proxMes = DateTimeUtils::incMes($registroConferencia->getDtRegistro());
        $existeProximo = $this->getDoctrine()->getRepository(RegistroConferencia::class)->findBy(['dtRegistro' => $proxMes, 'descricao' => $registroConferencia->getDescricao()]);
        if ($existeProximo) {
            throw new ViewException('Próximo registro já existe');
        } else {
            $novo = new RegistroConferencia();
            $novo->setCarteira($registroConferencia->getCarteira());
            $novo->setDescricao($registroConferencia->getDescricao());
            $novo->setDtRegistro($proxMes);
            $novo->setValor(null);
            $this->getRegistroConferenciaEntityHandler()->save($novo);
        }

    }

    /**
     * @return mixed
     */
    public function getRegistroConferenciaEntityHandler():RegistroConferenciaEntityHandler
    {
        return $this->registroConferenciaEntityHandler;
    }

    /**
     * @required
     * @param mixed $registroConferenciaEntityHandler
     */
    public function setRegistroConferenciaEntityHandler(RegistroConferenciaEntityHandler $registroConferenciaEntityHandler): void
    {
        $this->registroConferenciaEntityHandler = $registroConferenciaEntityHandler;
    }



}