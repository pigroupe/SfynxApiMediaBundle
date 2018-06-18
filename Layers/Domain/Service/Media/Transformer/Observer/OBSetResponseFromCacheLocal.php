<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;

/**
 * Class OBSetResponseFromCacheLocal
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBSetResponseFromCacheLocal extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;
    /** @var resource */
    protected static $fileinfo;

    /**
     * OBSetResponseFromCacheLocal constructor.
     */
    public function __construct()
    {
        self::$fileinfo = finfo_open(FILEINFO_MIME_TYPE);
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
            && $this->wfLastData->hasCachedLocaleImage
        ) {
            list($this->wfLastData->fileGetContents, $this->wfLastData->mimeType, $this->wfLastData->size, $this->wfLastData->date) = [
                file_get_contents($this->wfLastData->cachedImageSourcePath),
                finfo_file(self::$fileinfo, $this->wfLastData->cachedImageSourcePath),
                filesize($this->wfLastData->cachedImageSourcePath),
                \DateTime::createFromFormat('U', filemtime($this->wfLastData->cachedImageSourcePath))
            ];
        }

        return $this;
    }
}
