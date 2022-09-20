<?php

namespace App\Entity\Ecommerce;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\EntityHandler;
use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidade 'ClienteConfig'.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"clienteConfig", "clienteConfig_generated", "cliente", "entityId"},"enable_max_depth"=true},
 *     denormalizationContext={"groups"={"clienteConfig"},"enable_max_depth"=true},
 *
 *     itemOperations={
 *          "get"={"path"="/ecommerce/clienteConfig/{id}", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "put"={"path"="/ecommerce/clienteConfig/{id}", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "delete"={"path"="/ecommerce/clienteConfig/{id}", "security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     collectionOperations={
 *          "get"={"path"="/ecommerce/clienteConfig", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "post"={"path"="/ecommerce/clienteConfig", "security"="is_granted('ROLE_ECOMM_ADMIN')"}
 *     },
 *
 *     attributes={
 *          "pagination_items_per_page"=10,
 *          "formats"={"jsonld", "csv"={"text/csv"}}
 *     }
 *
 * )
 * @ApiFilter(PropertyFilter::class)
 * 
 * @ApiFilter(BooleanFilter::class, properties={
 *     "ativo": "exact",
 *     "cliente.ativo": "exact",
 * })
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "cliente": "exact"
 * })
 *
 * @ApiFilter(OrderFilter::class, properties={
 *     "id", 
 *     "updated", 
 *     "cliente.nome", 
 *     "ativo",
 *     "trayDtExpRefreshToken"
 * }, arguments={"orderParameterName"="order"})
 *
 * @EntityHandler(entityHandlerClass="App\EntityHandler\Ecommerce\ClienteConfigEntityHandler")
 *
 * @ORM\Entity(repositoryClass="App\Repository\Ecommerce\ClienteConfigRepository")
 * @ORM\Table(name="ecomm_cliente_config")
 *
 * @author Carlos Eduardo Pauluk
 */
class ClienteConfig implements EntityId
{

    use EntityIdTrait;

    
    /**
     * @ORM\Column(name="uuid", type="string", nullable=false, length=36)
     * @NotUppercase()
     * @Groups("clienteConfig")
     * @Assert\Length(min=36, max=36)
     *
     * @var string|null
     */
    public ?string $UUID = null;


    /**
     *
     * @ORM\ManyToOne(targetEntity="CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente")
     * @ORM\JoinColumn(name="cliente_id")
     * @Groups("clienteConfig")
     *
     * @var null|Cliente
     */
    public ?Cliente $cliente = null;
    

    /**
     * @ORM\Column(name="ativo", type="boolean", nullable=false)
     * @Groups("clienteConfig")
     */
    public ?bool $ativo = true;

    
    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("clienteConfig")
     */
    public ?array $jsonData = null;


    /**
     * @ORM\Column(name="tray_dt_exp_access_token", type="datetime")
     * @Groups("clienteConfig")
     * @var null|\DateTime
     */
    public ?\DateTime $trayDtExpAccessToken = null;

    
    


}
