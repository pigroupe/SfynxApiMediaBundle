<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification;

use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\SpecificationBundle\Specification\AbstractSpecification;

/**
 * Class SpecIsCacheFromStorage
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Specification
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class SpecIsCacheFromStorage extends AbstractSpecification
{
    /**
     * return true if the command is validated
     *
     * @param stdClass $object
     * @return bool
     */
    public function isSatisfiedBy(stdClass $object): bool
    {
        return isset($object->options['cacheStorageProvider'])
            && !empty($object->options['cacheStorageProvider'])
            && $object->options['cacheStorageProvider'] instanceof FilesystemInterface;
    }

    /**
     * @param array $options
     * @param Media $media
     * @return \StdClass
     */
    public static function setObject($options)
    {
        $object = new \StdClass();
        $object->options = $options;

        return $object;
    }
}
