<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;



/**
 * Media
 *
 * @ORM\Table(name="sfynx_apimedia",
 *    indexes={
 *        @ORM\Index(name="sfynx_apimedia_source", columns={"source"}),
 *        @ORM\Index(name="sfynx_apimedia_mimetype", columns={"mime_type"})
 *    },
 *    uniqueConstraints={@ORM\UniqueConstraint(name="sfynx_apimedia_reference", columns={"reference"})}
 * )
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Media  
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $source;

    /**
     * @var string
     * @ORM\Column(name="ip_source", type="string", length=64, nullable=true)
     */
    protected $ipSource;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $reference;

    /**
     * @var string
     * @ORM\Column(name="reference_prefix", type="string", nullable=true)
     */
    protected $referencePrefix;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $extension;

    /**
     * @var string
     * @ORM\Column(name="provider_service_name", type="string")
     */
    protected $providerServiceName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $size;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $quality = 95;

    /**
     * @var string
     * @ORM\Column(name="mime_type", type="string", length=255)
     */
    protected $mimeType;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $metadata;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $signing;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var UploadedFile
     */
    protected $uploadedFile;

    /**
     * On create
     *
     * @ORM\PrePersist()
     */
    public function onCreate()
    {
        $now = new \DateTime();
        $this
            ->setCreatedAt($now)
        ;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setEnabled(true);
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getReference();
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'                  => $this->getId(),
            'source'              => $this->getSource(),
            'ipSource'            => $this->getIpSource(),
            'reference'           => $this->getReference(),
            'extension'           => $this->getExtension(),
            'providerServiceName' => $this->getProviderServiceName(),
            'name'                => $this->getName(),
            'description'         => $this->getDescription(),
            'size'                => $this->getSize(),
            'mimeType'            => $this->getMimeType(),
            'enabled'             => $this->getEnabled(),
            'createdAt'           => $this->getCreatedAt()->format('c'),
            'metadata'            => $this->getMetadata(),
            'signing'             => $this->getSigning(),
            'quality'             => $this->getQuality(),
        ];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return $this
     */
    public function setSource($source): Media
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set ip source
     *
     * @param string $ipSource
     * @return $this
     */
    public function setIpSource($ipSource): Media
    {
        $this->ipSource = $ipSource;
        return $this;
    }

    /**
     * Get ip source
     *
     * @return string
     */
    public function getIpSource()
    {
        return $this->ipSource;
    }

    /**
     * Set reference
     *
     * @param string $reference
     * @return $this
     */
    public function setReference($reference): Media
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference prefix
     *
     * @param string $referencePrefix
     * @return $this
     */
    public function setReferencePrefix($referencePrefix): Media
    {
        $this->referencePrefix = $referencePrefix;
        return $this;
    }

    /**
     * Get reference prefix
     *
     * @return string
     */
    public function getReferencePrefix()
    {
        return $this->referencePrefix;
    }

    /**
     * Set extension
     *
     * @param string $extension
     * @return $this
     */
    public function setExtension($extension): Media
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set providerServiceName
     *
     * @param string $providerServiceName
     * @return $this
     */
    public function setProviderServiceName($providerServiceName): Media
    {
        $this->providerServiceName = $providerServiceName;
        return $this;
    }

    /**
     * Get providerServiceName
     *
     * @return string
     */
    public function getProviderServiceName()
    {
        return $this->providerServiceName;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name): Media
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description): Media
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return $this
     */
    public function setSize($size): Media
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set quality
     *
     * @param integer $quality
     * @return $this
     */
    public function setQuality($quality): Media
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Get quality
     *
     * @return integer
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType): Media
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return $this
     */
    public function setEnabled($enabled): Media
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set metadata
     *
     * @param array $metadata
     * @return $this
     */
    public function setMetadata(array $metadata): Media
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Get metadata
     *
     * @param string $key
     * @return array
     */
    public function getMetadata($key = null)
    {
        if (null === $key) {
            return $this->metadata;
        }
        return (isset($this->metadata[$key]) ? $this->metadata[$key] : null);
    }

    /**
     * Set signing
     *
     * @param array signing
     * @return $this
     */
    public function setSigning(array $signing): Media
    {
        $this->signing = $signing;
        return $this;
    }

    /**
     * Get signing
     *
     * @param string $key
     * @return array
     */
    public function getSigning($key = null)
    {
        if (null === $key) {
            return $this->signing;
        }
        return (isset($this->signing[$key]) ? $this->signing[$key] : null);
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt): Media
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set uploaded file.
     *
     * @param UploadedFile $uploadedFile
     * @return $this
     */
    public function setUploadedFile(UploadedFile $uploadedFile): Media
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Returns uploaded file.
     *
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }
}
