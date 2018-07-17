<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;
use Gaufrette\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ImageMedia;

/**
 * Class OBSetImageLocaleIfNoExistedInCacheStorage
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBSetImageLocaleIfNoExistedInCacheStorage extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;
    /** @var Media  */
    protected $media;
    /** @var FilesystemInterface  */
    protected $storageProvider;

    /**
     * OBSetResponseFromCacheStorage constructor.
     * @param Media $media
     */
    public function __construct(Media $media, FilesystemInterface $storageProvider)
    {
        $this->media = $media;
        $this->storageProvider = $storageProvider;
    }

    /**
     * Set Parameters and specifications used by observer
     *
     * @return AbstractObserver
     * @throws WorkflowException
     */
    protected function execute(): AbstractObserver
    {
        if (!$this->wfLastData->hasGetOriginalContentRequested
            && (
                (!$this->wfLastData->hasCacheFromStorage && ! $this->wfLastData->hasCachedLocaleImage)
                ||
                ($this->wfLastData->hasCacheFromStorage && !$this->wfLastData->hasCachedStorageImage)
            )
        ) {
            $options = array_filter($this->wfCommand->toArray());

            $originalContent = $this->storageProvider->read($options['storage_key']);
            $imageMedia = $this->createImageMedia($originalContent, $this->media);
            $reflectionClass = new \ReflectionClass($imageMedia);

            foreach ($options as $function => $argument) {
                if (!$reflectionClass->hasMethod($function)) {
                    continue;
                }
                $reflectionMethod = $reflectionClass->getMethod($function);
                $parameters = $reflectionMethod->getParameters();
                if (!count($parameters)) {
                    $imageMedia->$function();
                } elseif (count($parameters) === 1) {
                    $imageMedia->$function($argument);
                } else {
                    $arguments = [];
                    foreach ($parameters as $parameter) {
                        $arguments[$parameter->getName()] = isset($options[$parameter->getName()]) ?
                            $options[$parameter->getName()] :
                            null;
                    }
                    call_user_func_array(array($imageMedia, $function), $arguments);
                }
            }

            $imageMedia->quality($this->media->getQuality())->save($this->wfLastData->cachedImageSourcePath);

            $this->wfLastData->hasCachedLocaleImage = true;
        }

        return $this;
    }

    /**
     * Create image media
     *
     * @param File $originalContent
     * @param Media $media
     * @return ImageMedia
     */
    protected function createImageMedia(string $originalContent, Media $media): ImageMedia
    {
        $OutputPath = $this->getOutputPath($media);
        $this->createOutputPath($OutputPath);
        file_put_contents($OutputPath, $originalContent);

        return new ImageMedia($media, $OutputPath);
    }

    /**
     * @param Media $media
     * @return string
     */
    protected function getOutputPath(Media $media): string
    {
        return sprintf('%s/%s.%s.tmp',
            $this->wfCommand->cacheDirectory,
            $this->media->getReference(),
            $this->media->getExtension()
        );
    }

    /**
     * @param string $OutputPath
     */
    protected function createOutputPath(string $OutputPath): void
    {
        $fs = new SymfonyFilesystem();
        if (!$fs->exists($OutputPath)) {
            if (!$fs->exists($this->wfCommand->cacheDirectory)) {
                $fs->mkdir($this->wfCommand->cacheDirectory, 0770);
            }
        }
    }
}
