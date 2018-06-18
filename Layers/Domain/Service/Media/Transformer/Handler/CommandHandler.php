<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Handler;

use Exception;
use Sfynx\CoreBundle\Layers\Application\Command\Handler\Generalisation\Interfaces\CommandHandlerInterface;
use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\Interfaces\CommandInterface;
use Sfynx\CoreBundle\Layers\Application\Command\Workflow\Generalisation\Interfaces\CommandWorkflowInterface;
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
    /** @var int */
    protected $size = 0;
    /** @var Datetime */
    protected $date = null;
    /** @var int */
    protected $sharedMaxAge;
    /** @var int */
    protected $maxAge;

    /**
     * @param CommandWorkflowInterface $workflowCommand
     */
    public function __construct(CommandWorkflowInterface $workflowCommand)
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
                $this->$attribut = end($this->workflowCommand->getData()->$attribut);
            }
        }

        foreach (['maxAge', 'sharedMaxAge'] as $attribut) {
            if (property_exists($command, $attribut)) {
                $this->$attribut = $command->$attribut;
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
        $response = (new ResponseMedia())
            ->setContent($this->fileGetContents)
            ->setContentType($this->mimeType)
            ->setContentLength($this->size)
            ->setLastModifiedAt($this->date)
        ;

        (null !== $this->sharedMaxAge) ? $response->setSharedMaxAge($this->sharedMaxAge): false;
        (null !== $this->maxAge) ? $response->setMaxAge($this->maxAge): false;

        return $response;
    }
}
