<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification;

use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\SpecificationBundle\Specification\AbstractSpecification;

/**
 * Class SpecIsReturnWithoutResponse
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Specification
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class SpecIsReturnWithoutResponse extends AbstractSpecification
{
    /**
     * return true if the command is validated
     *
     * @param stdClass $object
     * @return bool
     */
    public function isSatisfiedBy(stdClass $object): bool
    {
        return property_exists($object->wfCommand, 'noresponse') && (1 == $object->wfCommand->noresponse);
    }

    /**
     * @param mixed $wfCommand
     * @return \StdClass
     */
    public static function setObject($wfCommand)
    {
        $object = new \StdClass();
        $object->wfCommand = $wfCommand;

        return $object;
    }
}
