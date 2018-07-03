<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification;

use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\SpecificationBundle\Specification\AbstractSpecification;

/**
 * Class SpecIsCacheLocale
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Specification
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class SpecIsCacheLocale extends AbstractSpecification
{
    /**
     * return true if the command is validated
     *
     * @param stdClass $object
     * @return bool
     */
    public function isSatisfiedBy(stdClass $object): bool
    {
        return file_exists($object->sourcePath);
    }

    /**
     * @param mixed $sourcePath
     * @return \StdClass
     */
    public static function setObject($sourcePath)
    {
        $object = new \StdClass();
        $object->sourcePath = $sourcePath;

        return $object;
    }
}
