<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;

/**
 * Class OBDeleteCacheLocaleFileIfCacheStorage
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBDeleteCacheLocaleFileIfCacheStorage extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;

    /**
     * OBCreateCacheStorageFile constructor.
     */
    public function __construct()
    {}

    /**
     * Set Parameters and specifications used by observer
     *
     * @return AbstractObserver
     * @throws WorkflowException
     */
    protected function execute(): AbstractObserver
    {
        if (!$this->wfLastData->HasGetOriginalContentRequested
            && $this->wfLastData->HasCacheFromStorage
            && $this->wfLastData->hasCachedLocaleImage
        ) {
            $this->deleteCacheLocaleFileIfCacheInStorage($this->wfLastData->cachedImageSourcePath);
        }

        return $this;
    }

    /**
     * @param string $sourcePath
     * @return void
     */
    protected function deleteCacheLocaleFileIfCacheInStorage(string $sourcePath): void
    {
        if ($this->wfLastData->HasCacheFromStorage) {
            unlink($sourcePath);
        }
    }
}
