<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Gaufrette\FilesystemInterface;

use Sfynx\CoreBundle\Layers\Application\Command\Workflow\CommandWorkflow;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Generalisation\AbstractMediaTransformer;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\DefaultCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Adapter\CommandAdapter;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetRestResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Handler\CommandHandler;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\RestResolver;
use Sfynx\CrawlerBundle\Crawler\Transformer\Doctrine2OtherTransformer;

class RestMediaTransformer extends AbstractMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return RestResolver::FORMATS;
    }

    /**
     * {@inheritdoc}
     */
    public function process(FilesystemInterface $storageProvider, Media $media, array $options = [])
    {
        // 1. Transform options to Command.
        $adapter = new CommandAdapter(new DefaultCommand());
        $command = $adapter->createCommandFromResolver(
            new RestResolver($options)
        );

        // 2. Implement the command workflow
        $workflowCommand = (new CommandWorkflow())
            ->attach(new OBSetRestResponseMedia($media, $storageProvider))
        ;

        // 3. Implement handler decorator to apply the command workflow from the command
        $this->commandHandler = new CommandHandler($workflowCommand);
        $this->commandHandler->process($command);

        return $this->commandHandler->createResponseMedia();
    }
}
