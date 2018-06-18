<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;

/**
 * Class OBSetResponseFromCacheStorage
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBSetResponseFromCacheStorage extends AbstractObserver
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
            && $this->wfLastData->hasCacheFromStorage
            && $this->wfLastData->hasCachedStorageImage
        ) {
            $cacheContent = $this->storageProvider->read($this->wfLastData->cacheStorageIdentifier);

            if (!$this->wfLastData->hasReturnContentWithoutResponse) {
                list($this->wfLastData->fileGetContents, $this->wfLastData->mimeType, $this->wfLastData->size, $this->wfLastData->date) = [
                    $cacheContent,
                    $this->media->getMimeType(),
                    $this->media->getSize(),
                    $this->media->getCreatedAt(),
                ];

                return $this;
            }

            list($this->wfLastData->fileGetContents, $this->wfLastData->mimeType, $this->wfLastData->size, $this->wfLastData->date) = [
                json_encode(['response' => 'ok']),
                'application/json',
                0,
                $this->media->getCreatedAt(),
            ];
        }

        return $this;
    }
}
