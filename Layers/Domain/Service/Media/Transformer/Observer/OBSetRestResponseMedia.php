<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;
use Gaufrette\FilesystemInterface;

use Sfynx\CrawlerBundle\Crawler\Transformer\Doctrine2OtherTransformer;
use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsGetOriginalContent;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\DefaultCommand;

/**
 * Class OBSetRestResponseMedia
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBSetRestResponseMedia extends AbstractObserver
{
    /** @var DefaultCommand */
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
        $export = Doctrine2OtherTransformer::export($this->media, $this->wfCommand->format);

        list($this->wfLastData->fileGetContents, $this->wfLastData->mimeType, $this->wfLastData->size, $this->wfLastData->date) = [
            $export->getContent(),
            sprintf(
                '%s; charset=UTF-8',
                $export->getContentType()
            ),
            null,
            $this->media->getCreatedAt()
        ];

        return $this;
    }
}
