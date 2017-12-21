<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\MetadataExtractor;

class DefaultMetadataExtractor implements MetadataExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkMimeType($mimeType)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($mediaPath)
    {
        return [];
    }
}
