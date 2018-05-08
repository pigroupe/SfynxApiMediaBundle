<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Gaufrette\FilesystemInterface;

use Sfynx\CoreBundle\Layers\Application\Command\Workflow\CommandWorkflow;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\DefaultCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Adapter\CommandAdapter;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetDefaultResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Handler\CommandHandler;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\DocumentResolver;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\UnavailableTransformationException;

class DocumentMediaTransformer extends AbstractMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return DocumentResolver::FORMATS;
    }

    /**
     * {@inheritdoc}
     */
    public function process(FilesystemInterface $storageProvider, Media $media, array $options = [])
    {
        // 1. Transform options to Command.
        $adapter = new CommandAdapter(new DefaultCommand());
        $command = $adapter->createCommandFromResolver(
            new DocumentResolver($options)
        );

        // 2. Implement the command workflow
        $Observer1 = new OBSetDefaultResponseMedia($media, $storageProvider);
        $workflowCommand = (new CommandWorkflow())
            ->attach($Observer1)
            ;

        // 3. Implement handler decorator to apply the command workflow from the command
        $this->commandHandler = new CommandHandler($workflowCommand);
        $this->commandHandler->process($command);

        return $this->commandHandler->createResponseMedia();
    }
}
