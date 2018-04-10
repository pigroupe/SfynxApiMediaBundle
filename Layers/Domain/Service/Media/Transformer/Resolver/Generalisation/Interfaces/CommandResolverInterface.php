<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation\Interfaces;

/**
 * Interface CommandResolverInterface
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Transformer\Resolver\Generalisation\Interfaces
 */
interface CommandResolverInterface
{
    /**
     * @return mixed
     */
    public function getResolverParameters();
}
