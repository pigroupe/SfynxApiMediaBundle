<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Adapter\Generalisation\Interfaces;

use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\Interfaces\CommandInterface;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation\Interfaces\CommandResolverInterface;

/**
 * Interface CommandAdapterInterface
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Transformer\Adapter\Generalisation\Interfaces
 */
interface CommandAdapterInterface
{
    /**
     * @param CommandResolverInterface $resolver
     * @return mixed
     */
    public function createCommandFromResolver(CommandResolverInterface $resolver): CommandInterface;
}