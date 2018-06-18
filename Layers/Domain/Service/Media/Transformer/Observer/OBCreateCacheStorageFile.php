<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;

/**
 * Class OBCreateCacheStorageFile
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBCreateCacheStorageFile extends AbstractObserver
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
        if (!$this->wfLastData->hasGetOriginalContentRequested
            && $this->wfLastData->hasCacheFromStorage
            && ! $this->wfLastData->hasCachedStorageImage
        ) {
            $this->createCacheStorageFile($this->wfLastData->fileGetContents, $this->wfLastData->cacheStorageIdentifier);
            $this->wfLastData->hasCachedStorageImage = true;
        }

        return $this;
    }

    /**
     * @param string $fileGetContents
     * @param string $storageIdentifier
     */
    protected function createCacheStorageFile(string $fileGetContents, string $storageIdentifier): void
    {
        if ($this->wfLastData->hasCacheFromStorage) {
            if ($this->wfLastData->cacheProviderServiceName->has($storageIdentifier)) {
                $this->wfLastData->cacheProviderServiceName->delete($storageIdentifier);
            }
            $this->wfLastData->cacheProviderServiceName->write($storageIdentifier, $fileGetContents);
        }
    }
}
