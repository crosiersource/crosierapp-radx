<?php

namespace App\Entity\RH;

use CrosierSource\CrosierLibBaseBundle\Doctrine\Annotations\NotUppercase;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityId;
use CrosierSource\CrosierLibBaseBundle\Entity\EntityIdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 *
 * @ORM\Entity(repositoryClass="App\Repository\RH\ColaboradorRepository")
 * @ORM\Table(name="rh_colaborador")
 * @Vich\Uploadable()
 *
 * @author Carlos Eduardo Pauluk
 */
class Colaborador implements EntityId
{

    use EntityIdTrait;

    /**
     *
     * @ORM\Column(name="cpf", type="string")
     * @var null|string
     *
     * @Groups("entity")
     */
    public ?string $cpf = null;

    /**
     *
     * @ORM\Column(name="nome", type="string")
     * @var null|string
     *
     * @Groups("entity")
     */
    public ?string $nome = null;

    /**
     * Informa se o colaborador está trabalhando atualmente na empresa.
     *
     * @ORM\Column(name="atual", type="boolean")
     * @var null|bool
     *
     * @Groups("entity")
     */
    public ?bool $atual = null;

    /**
     *
     * @ORM\Column(name="json_data", type="json")
     * @var null|array
     * @NotUppercase()
     * @Groups("entity")
     */
    public ?array $jsonData = null;


    /**
     * @Vich\UploadableField(mapping="rh_colaborador_foto", fileNameProperty="imageName")
     * @var null|File
     */
    private ?File $imageFile = null;

    /**
     * @ORM\Column(name="image_name", type="string")
     * @Groups("entity")
     * @NotUppercase()
     * @var null|string
     */
    public ?string $imageName = null;


    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }


    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     * @return Colaborador
     * @throws \Exception
     */
    public function setImageFile(?File $imageFile = null): Colaborador
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new \DateTime();
        }
        return $this;
    }


}
