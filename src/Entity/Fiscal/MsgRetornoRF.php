<?php

namespace App\Entity\Fiscal;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade para mensagens de retorno da Receita Federal.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Fiscal\MsgRetornoRFRepository")
 * @ORM\Table(name="fis_msg_retorno_rf")
 *
 * @author Carlos Eduardo Pauluk
 */
class MsgRetornoRF implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="codigo", type="integer", nullable=false)
     */
    private $codigo;

    /**
     *
     * @ORM\Column(name="mensagem", type="string", nullable=false, length=2000)
     */
    private $mensagem;

    /**
     *
     * @ORM\Column(name="versao", type="string", nullable=false, length=10)
     */
    private $versao;

    /**
     * @return mixed
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $codigo
     * @return MsgRetornoRF
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMensagem()
    {
        return $this->mensagem;
    }

    /**
     * @param mixed $mensagem
     * @return MsgRetornoRF
     */
    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersao()
    {
        return $this->versao;
    }

    /**
     * @param mixed $versao
     * @return MsgRetornoRF
     */
    public function setVersao($versao)
    {
        $this->versao = $versao;
        return $this;
    }


}