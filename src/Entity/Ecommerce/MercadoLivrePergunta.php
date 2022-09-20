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
 * Entidade 'MercadoLivrePergunta'.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"mercadoLivrePergunta", "mercadoLivreItem", "cliente", "clienteConfig", "entityId"},"enable_max_depth"=true},
 *     denormalizationContext={"groups"={"mercadoLivrePergunta"},"enable_max_depth"=true},
 *
 *     itemOperations={
 *          "get"={"path"="/ecommerce/mercadoLivrePergunta/{id}", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "put"={"path"="/ecommerce/mercadoLivrePergunta/{id}", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "delete"={"path"="/ecommerce/mercadoLivrePergunta/{id}", "security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     collectionOperations={
 *          "get"={"path"="/ecommerce/mercadoLivrePergunta", "security"="is_granted('ROLE_ECOMM_ADMIN')"},
 *          "post"={"path"="/ecommerce/mercadoLivrePergunta", "security"="is_granted('ROLE_ECOMM_ADMIN')"}
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
 *     "status": "exact",
 * })
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "mercadoLivreItem.clienteConfig.cliente": "exact",
 * })
 * 
 * @ApiFilter(DateFilter::class, properties={"dtPergunta"})
 *
 * @ApiFilter(OrderFilter::class, properties={
 *     "id", 
 *     "updated", 
 *     "mercadoLivreItem.clienteConfig.cliente.nome",
 *     "status",
 *     "dtPergunta",
 * }, arguments={"orderParameterName"="order"})
 *
 * @EntityHandler(entityHandlerClass="App\EntityHandler\Ecommerce\MercadoLivrePerguntaEntityHandler")
 *
 * @ORM\Entity(repositoryClass="App\Repository\Ecommerce\MercadoLivrePerguntaRepository")
 * @ORM\Table(name="ecomm_ml_pergunta")
 *
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivrePergunta implements EntityId
{

    use EntityIdTrait;


    /**
     * @ORM\Column(name="uuid", type="string", nullable=false, length=36)
     * @NotUppercase()
     * @Groups("mercadoLivrePergunta")
     * @Assert\Length(min=36, max=36)
     *
     * @var string|null
     */
    public ?string $UUID = null;


    /**
     * Id da pergunta no ML.
     * 
     * @ORM\Column(name="mercadolivre_id", type="bigint", nullable=false)
     * @Groups("mercadoLivreItem")
     *
     * @var null|int
     */
    public ?int $mercadolivreId = null;

    
    /**
     *
     * @ORM\Column(name="dt_pergunta", type="datetime", nullable=false)
     * @Groups("mercadoLivrePergunta")
     *
     * @var null|\DateTime
     */
    public ?\DateTime $dtPergunta = null;

    
    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     * @Groups("mercadoLivrePergunta")
     *
     * @var null|string
     */
    public ?string $status = null;

    
    /**
     *
     * @ORM\ManyToOne(targetEntity="MercadoLivreItem")
     * @ORM\JoinColumn(name="ml_item_id")
     * @Groups("mercadoLivrePergunta")
     *
     * @var null|MercadoLivreItem
     */
    public ?MercadoLivreItem $mercadoLivreItem = null;


    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("mercadoLivrePergunta")
     */
    public ?array $jsonData = null;


}
