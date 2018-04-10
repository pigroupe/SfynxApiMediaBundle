<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Handler;

use Exception;
use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\Interfaces\CommandHandlerInterface;
use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\Interfaces\CommandInterface;
use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\Interfaces\WorkflowCommandInterface;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;

/**
 * Class CommandHandler.
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Transformer\Handler
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class CommandHandler implements CommandHandlerInterface
{
    /** @var WorkflowCommandInterface */
    protected $workflowCommand;
    /** @var string */
    protected $fileGetContents = '';
    /** @var string */
    protected $mimeType = '';
    /** @var array */
    protected $size = 0;
    /** @var string */
    protected $date = null;

    /**
     * @param WorkflowCommandInterface $workflowCommand
     */
    public function __construct(WorkflowCommandInterface $workflowCommand)
    {
        $this->workflowCommand = $workflowCommand;
    }

    /**
     * @param CommandInterface $command
     *
     * @return CommandHandlerInterface
     * @throws WorkflowException
     */
    public function process(CommandInterface $command): CommandHandlerInterface
    {
        // execute all observers in the wrokflow
        $this->workflowCommand->process($command);

        foreach (['fileGetContents', 'mimeType', 'size', 'date'] as $attribut) {
            if (property_exists($this->workflowCommand->getData(), $attribut)) {
                $this->fileGetContents = end($this->workflowCommand->getData()->fileGetContents);
            }
            if (property_exists($this->workflowCommand->getData(), 'mimeType')) {
                $this->mimeType = end($this->workflowCommand->getData()->mimeType);
            }
            if (property_exists($this->workflowCommand->getData(), 'size')) {
                $this->size = end($this->workflowCommand->getData()->size);
            }
            if (property_exists($this->workflowCommand->getData(), 'date')) {
                $this->date = end($this->workflowCommand->getData()->date);
            }
        }

        return $this;
    }

    /**
     * Create a response media
     *
     * @param string $content
     * @param string $mimeType
     * @param integer $size
     * @param \DateTime $date
     * @return ResponseMedia
     */
    public function createResponseMedia(): ResponseMedia
    {
        return (new ResponseMedia())
            ->setContent($this->fileGetContents)
            ->setContentType($this->mimeType)
            ->setContentLength($this->size)
            ->setLastModifiedAt($this->date)
            ;
    }
}
