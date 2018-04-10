<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Adapter;

use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\Interfaces\CommandInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Adapter\Generalisation\Interfaces\CommandAdapterInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation\Interfaces\CommandResolverInterface;

/**
 * Class CommandAdapter.
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Transformer\Adapter
 */
class CommandAdapter implements CommandAdapterInterface
{
    /** @var  CommandInterface */
    protected $command;

    public function __construct(CommandInterface $command)
    {
        $this->commmand = $command;
    }

    /**
     * @param CommandRequestInterface $request
     * @return CommandInterface
     */
    public function createCommandFromResolver(CommandResolverInterface $resolver): CommandInterface
    {
        $this->parameters = $resolver->getResolverParameters();

        foreach ((new \ReflectionObject($this->commmand))->getProperties() as $oProperty) {
            $oProperty->setAccessible(true);
            $value = isset($this->parameters[$oProperty->getName()]) ? $this->parameters[$oProperty->getName()] : $oProperty->getValue($this->commmand);
            $oProperty->setValue($this->commmand, $value);
        }

        return $this->commmand;
    }
}
