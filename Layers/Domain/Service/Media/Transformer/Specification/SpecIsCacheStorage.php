<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification;

use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\SpecificationBundle\Specification\AbstractSpecification;

/**
 * Class SpecIsCacheStorage
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Specification
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class SpecIsCacheStorage extends AbstractSpecification
{
    /** @var FilesystemInterface */
    protected $providerService;

    public function __construct(FilesystemInterface $ProviderService)
    {
        $this->providerService = $ProviderService;
    }

    /**
     * return true if the command is validated
     *
     * @param stdClass $object
     * @return bool
     */
    public function isSatisfiedBy(stdClass $object): bool
    {
        return $this->providerService->has($object->storageIdentifier);
    }

    /**
     * @param array $options
     * @param Media $media
     * @return \StdClass
     */
    public static function setObject($storageIdentifier)
    {
        $object = new \StdClass();
        $object->storageIdentifier = $storageIdentifier;

        return $object;
    }
}
