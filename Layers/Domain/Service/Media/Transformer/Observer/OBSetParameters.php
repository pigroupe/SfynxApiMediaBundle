<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Observer;

use Exception;
use stdClass;

use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Command\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command\MediaCommand;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsReturnWithoutResponse;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsGetOriginalContent;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheFromStorage;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheLocale;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Specification\SpecIsCacheStorage;

/**
 * Class OBSetParameters
 *
 * @category Sfynx\ApiMediaBundle\Layers
 * @package Domain
 * @subpackage Service\Media\Transformer\Observer
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBSetParameters extends AbstractObserver
{
    /** @var MediaCommand */
    protected $wfCommand;
    /** @var Media  */
    protected $media;

    const excludeList = [
        'noresponse',
        'cacheStorageProvider',
        'cacheDirectory',
        'maxAge',
        'sharedMaxAge',
        'signingKey',
        'format',
        'storage_key',
    ];

    /**
     * OBSetParameters constructor.
     * @param Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Set Parameters and specifications used by observer
     *
     * @return AbstractObserver
     * @throws WorkflowException
     */
    protected function execute(): AbstractObserver
    {
        $options = array_filter($this->wfCommand->toArray());

        $this->wfLastData->hasReturnContentWithoutResponse = (new SpecIsReturnWithoutResponse())
            ->isSatisfiedBy(SpecIsReturnWithoutResponse::setObject($this->wfCommand));

        $this->wfLastData->cachedImageSourcePath = $this->getCachedImageSourcePath(
            $this->media->getReference(),
            $options,
            false
        );

        $this->wfLastData->hasGetOriginalContentRequested = (new SpecIsGetOriginalContent())
            ->isSatisfiedBy(SpecIsGetOriginalContent::setObject(
                $this->wfCommand->format,
                $this->media->getExtension(),
                count($options)
            ));

        $this->wfLastData->hasCacheFromStorage = (new SpecIsCacheFromStorage())
            ->isSatisfiedBy(SpecIsCacheFromStorage::setObject($options));

        $this->wfLastData->hasCachedLocaleImage = (new SpecIsCacheLocale())
            ->isSatisfiedBy(SpecIsCacheLocale::setObject($this->wfLastData->cachedImageSourcePath));

        if (!$this->wfLastData->hasGetOriginalContentRequested && $this->wfLastData->hasCacheFromStorage) {
            $this->wfLastData->cacheStorageIdentifier = $this->getCachedImageSourcePath(
                $this->media->getReference(),
                $options,
                true
            );
            $this->wfLastData->cacheProviderServiceName = $this->wfCommand->cacheStorageProvider;

            $this->wfLastData->hasCachedStorageImage = (new SpecIsCacheStorage($this->wfLastData->cacheProviderServiceName))
                ->isSatisfiedBy(SpecIsCacheStorage::setObject($this->wfLastData->cacheStorageIdentifier));
        }

        return $this;
    }

    /**
     * Get the cached image source path based on the media and the requested options
     *
     * @param string $reference
     * @param bool $fromStorage
     * @return string
     */
    protected function getCachedImageSourcePath(string $reference, array $options, bool $fromStorage = false)
    {
        foreach (self::excludeList as $item) {
            if (!empty($options[$item])) {
                unset($options[$item]);
            }
        }
        $imageCacheName = sprintf('%s_%s.%s',
            $reference,
            sprintf("%u", crc32(serialize($options))),
            $this->wfCommand->format
        );
        if ($fromStorage) {
            $imageCachePath = sprintf('%s%s', str_replace(basename($this->wfCommand->storage_key), '', $this->wfCommand->storage_key), $imageCacheName);
        } else {
            $imageCachePath = sprintf('%s/%s', $this->wfCommand->cacheDirectory, $imageCacheName);
        }

        return $imageCachePath;
    }
}
