<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\MetadataExtractor;

interface MetadataExtractorInterface
{
    /**
     * Check the mimeType.
     *
     * @param string $file
     * @return boolean
     */
    public function checkMimeType($mimeType);

    /**
     * Extract media metadata
     *
     * @param string $mediaPath
     * @return array
     */
    public function extract($mediaPath);
}
