<?php

namespace App\Entity\Ecommerce;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidade 'TrayVenda'.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"trayVenda", "clienteConfig", "cliente", "entityId"},"enable_max_depth"=true},
 *     denormalizationContext={"groups"={"trayVenda"},"enable_max_depth"=true},
 *
 *     itemOperations={
 *          "get"={"path"="/ecommerce/trayVenda/{id}", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "put"={"path"="/ecommerce/trayVenda/{id}", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "delete"={"path"="/ecommerce/trayVenda/{id}", "security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     collectionOperations={
 *          "get"={"path"="/ecommerce/trayVenda", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "post"={"path"="/ecommerce/trayVenda", "security"="is_granted('ROLE_ECOMM_ADMIN')"}
 *     },
 *
 *     attributes={
 *          "pagination_items_per_page"=10,
 *          "formats"={"jsonld", "csv"={"text/csv"}}
 *     }
 *
 * )
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter(BooleanFilter::class, properties={
 *     "atual": "exact",
 * })
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "clienteConfig.cliente": "exact",
 *     "statusTray": "partial",
 *     "idTray": "exact",
 * })
 * 
 * @ApiFilter(DateFilter::class, properties={"dtVenda"})
 *
 * @ApiFilter(OrderFilter::class, properties={
 *     "id", 
 *     "updated", 
 *     "clienteConfig.cliente.nome",
 *     "statusTray",
 *     "dtVenda",
 *     "idTray",
 *     "pointSale",
 *     "valorTotal"
 * }, arguments={"orderParameterName"="order"})
 *
 * @EntityHandler(entityHandlerClass="App\EntityHandler\Ecommerce\TrayVendaEntityHandler")
 *
 * @ORM\Entity(repositoryClass="App\Repository\Ecommerce\TrayVendaRepository")
 * @ORM\Table(name="ecomm_tray_venda")
 *
 * @author Carlos Eduardo Pauluk
 */
class TrayVenda implements EntityId
{

    use EntityIdTrait;


    /**
     * @ORM\Column(name="uuid", type="string", nullable=false, length=36)
     * @NotUppercase()
     * @Groups("trayVenda")
     * @Assert\Length(min=36, max=36)
     *
     * @var string|null
     */
    public ?string $UUID = null;

    
    /**
     * @ORM\Column(name="id_tray", type="string", nullable=false)
     * @Groups("trayVenda")
     *
     * @var null|string
     */
    public ?string $idTray = null;

    
    /**
     *
     * @ORM\Column(name="dt_venda", type="datetime", nullable=false)
     * @Groups("trayVenda")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtVenda = null;

    
    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     * @Groups("trayVenda")
     *
     * @var null|string
     */
    public ?string $statusTray = null;

    
    /**
     * @ORM\Column(name="point_sale", type="string", nullable=false)
     * @Groups("trayVenda")
     *
     * @var null|string
     */
    public ?string $pointSale = null;


    /**
     * @ORM\Column(name="cliente_nome", type="string", nullable=false)
     * @Groups("trayVenda")
     *
     * @var null|string
     */
    public ?string $clienteNome = null;

    
    /**
     *
     * @ORM\Column(name="valor_total", type="decimal")
     * @Groups("trayVenda")
     *
     * @var null|float
     */
    public ?float $valorTotal = null;


    /**
     *
     * @ORM\ManyToOne(targetEntity="ClienteConfig")
     * @ORM\JoinColumn(name="cliente_config_id")
     * @Groups("trayVenda")
     *
     * @var null|ClienteConfig
     */
    public ?ClienteConfig $clienteConfig = null;


    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("trayVenda")
     */
    public ?array $jsonData = null;


}
