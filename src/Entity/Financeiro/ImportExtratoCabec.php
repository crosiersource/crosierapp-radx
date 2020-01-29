<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entidade 'ImportExtratoCabec'.
 *
 * Registra as relações de-para entre campos da fin_movimentacao e campos do CSV.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\ImportExtratoCabecRepository")
 * @ORM\Table(name="fin_import_extrato_cabec")
 *
 * @author Carlos Eduardo Pauluk
 */
class ImportExtratoCabec implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="tipo_extrato", type="string", nullable=false, length=100)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $tipoExtrato;

    /**
     *
     * @ORM\Column(name="campo_sistema", type="string", nullable=false, length=100)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $campoSistema;

    /**
     *
     * @ORM\Column(name="campos_cabecalho", type="string", nullable=false, length=200)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $camposCabecalho;

    /**
     *
     * @ORM\Column(name="formato", type="string", nullable=true, length=100)
     * @Groups("entity")
     *
     * @var string|null
     */
    private $formato;

    /**
     * @return null|string
     */
    public function getTipoExtrato(): ?string
    {
        return $this->tipoExtrato;
    }

    /**
     * @param null|string $tipoExtrato
     * @return ImportExtratoCabec
     */
    public function setTipoExtrato(?string $tipoExtrato): ImportExtratoCabec
    {
        $this->tipoExtrato = $tipoExtrato;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCampoSistema(): ?string
    {
        return $this->campoSistema;
    }

    /**
     * @param null|string $campoSistema
     * @return ImportExtratoCabec
     */
    public function setCampoSistema(?string $campoSistema): ImportExtratoCabec
    {
        $this->campoSistema = $campoSistema;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCamposCabecalho(): ?string
    {
        return $this->camposCabecalho;
    }

    /**
     * @param null|string $camposCabecalho
     * @return ImportExtratoCabec
     */
    public function setCamposCabecalho(?string $camposCabecalho): ImportExtratoCabec
    {
        $this->camposCabecalho = $camposCabecalho;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormato(): ?string
    {
        return $this->formato;
    }

    /**
     * @param null|string $formato
     * @return ImportExtratoCabec
     */
    public function setFormato(?string $formato): ImportExtratoCabec
    {
        $this->formato = $formato;
        return $this;
    }


}

