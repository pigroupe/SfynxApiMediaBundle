<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Gaufrette\FilesystemInterface;

use Sfynx\CoreBundle\Layers\Application\Command\Workflow\CommandWorkflow;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Adapter\CommandAdapter;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\MediaResolver;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Handler\CommandHandler;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetParameters;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetResponseFromeOriginalStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetResponseFromCacheStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetImageLocaleIfNoExistedInCacheStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBSetResponseFromCacheLocal;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBCreateCacheStorageFile;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer\OBDeleteCacheLocaleFileIfCacheStorage;

class ImageMediaTransformer extends AbstractMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return MediaResolver::FORMATS;
    }

    /**
     * {@inheritdoc}
     */
    public function process(FilesystemInterface $storageProvider, Media $media, array $options = [])
    {
        // 1. Transform options to Command.
        $adapter = new CommandAdapter(new MediaCommand());
        $command = $adapter->createCommandFromResolver(
            new MediaResolver($options)
        );

        // 2. Implement the command workflow
        $Observer1 = new OBSetParameters($media);
        $Observer2 = new OBSetResponseFromeOriginalStorage($media, $storageProvider);
        $Observer3 = new OBSetResponseFromCacheStorage($media, $storageProvider);
        $Observer4 = new OBSetImageLocaleIfNoExistedInCacheStorage($media, $storageProvider);
        $Observer5 = new OBSetResponseFromCacheLocal();
        $Observer6 = new OBCreateCacheStorageFile();
        $Observer7 = new OBDeleteCacheLocaleFileIfCacheStorage();

        $workflowCommand = (new CommandWorkflow())
            ->attach($Observer1)
            ->attach($Observer2)
            ->attach($Observer3)
            ->attach($Observer4)
            ->attach($Observer5)
            ->attach($Observer6)
            ->attach($Observer7)
        ;

        // 3. Implement handler decorator to apply the command workflow from the command
        $this->commandHandler = new CommandHandler($workflowCommand);
        $this->commandHandler->process($command);

        return $this->commandHandler->createResponseMedia();
    }
}
