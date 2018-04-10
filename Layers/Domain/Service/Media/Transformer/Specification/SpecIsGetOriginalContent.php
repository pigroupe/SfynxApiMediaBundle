<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification;

use stdClass;
use Sfynx\SpecificationBundle\Specification\AbstractSpecification;

/**
 * Class SpecIsGetOriginalContent
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Specification
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class SpecIsGetOriginalContent extends AbstractSpecification
{
    /**
     * return true if the command is validated
     *
     * @param stdClass $object
     * @return bool
     */
    public function isSatisfiedBy(stdClass $object): bool
    {
        return strtolower($object->format) === strtolower($object->media_extension)
            && $object->count_option === 4;
    }

    /**
     * @param array $options
     * @param Media $media
     * @return \StdClass
     */
    public static function setObject($format, $extension, $countOption)
    {
        $object = new \StdClass();
        $object->format = $format;
        $object->media_extension = $extension;
        $object->count_option = $countOption;

        return $object;
    }
}
