<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsGetOriginalContent;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;

/**
 * Class OBSetResponseFromeOriginalStorage
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBSetResponseFromeOriginalStorage extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;
    /** @var Media  */
    protected $media;
    /** @var FilesystemInterface  */
    protected $storageProvider;

    /**
     * OBSetResponseFromeOriginalStorage constructor.
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
        if (!property_exists($this->wfLastData, 'hasGetOriginalContentRequested')) {
            $this->wfLastData->hasGetOriginalContentRequested = (new SpecIsGetOriginalContent())
                ->isSatisfiedBy(SpecIsGetOriginalContent::setObject(
                    $this->wfCommand->format,
                    $this->media->getExtension(),
                    count(array_filter($this->wfCommand->toArray()))
                ));
        }

        if ($this->wfLastData->hasGetOriginalContentRequested) {
            $originalContent = $this->storageProvider->read($this->wfCommand->storage_key);

            list($this->wfLastData->fileGetContents, $this->wfLastData->mimeType, $this->wfLastData->size, $this->wfLastData->date) = [
                $originalContent,
                $this->media->getMimeType(),
                $this->media->getSize(),
                $this->media->getCreatedAt()
            ];
        }

        return $this;
    }
}
