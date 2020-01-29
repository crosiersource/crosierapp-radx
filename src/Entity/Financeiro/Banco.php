<?php

namespace App\Entity\Financeiro;

use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidade 'Banco'.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Financeiro\BancoRepository")
 * @ORM\Table(name="fin_banco")
 *
 * @author Carlos Eduardo Pauluk
 */
class Banco implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="codigo_banco", type="integer", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Range(min = 1)
     * @Groups("entity")
     *
     * @var int|null
     */
    private $codigoBanco;

    /**
     *
     * @ORM\Column(name="nome", type="string", nullable=false, length=200)
     * @Assert\NotBlank()
     * @Groups("entity")
     *
     * @var string|null
     */
    private $nome;

    /**
     * Para poder filtrar exibição na view.
     *
     * @ORM\Column(name="utilizado", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups("entity")
     *
     * @var bool|null
     */
    private $utilizado = false;

    /**
     * @param bool $format
     * @return int|null
     */
    public function getCodigoBanco(bool $format = false)
    {
        if ($format) {
            return str_pad($this->codigoBanco, 3, '0', STR_PAD_LEFT);
        }

        return $this->codigoBanco;
    }

    /**
     * @param int|null $codigoBanco
     * @return Banco
     */
    public function setCodigoBanco(?int $codigoBanco): Banco
    {
        $this->codigoBanco = $codigoBanco;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * @param null|string $nome
     * @return Banco
     */
    public function setNome(?string $nome): Banco
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescricaoMontada(): string
    {
        return $this->getCodigoBanco(true) . ' - ' . $this->getNome();
    }

    /**
     * @return bool|null
     */
    public function getUtilizado(): ?bool
    {
        return $this->utilizado;
    }

    /**
     * @param bool|null $utilizado
     * @return Banco
     */
    public function setUtilizado(?bool $utilizado): Banco
    {
        $this->utilizado = $utilizado;
        return $this;
    }

}

