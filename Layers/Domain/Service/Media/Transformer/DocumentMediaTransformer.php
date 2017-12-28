<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Gaufrette\Filesystem;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;
use Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception\UnavailableTransformationException;

class DocumentMediaTransformer extends AbstractMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('pdf', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'odt');
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = [])
    {
        $responseMedia = new ResponseMedia();

        if ($options['format'] !== $media->getExtension()) {
            throw new UnavailableTransformationException($options);
        }

        $responseMedia
            ->setContent($storageProvider->read($options['storage_key']))
            ->setContentType($media->getMimeType())
            ->setContentLength($media->getSize())
            ->setLastModifiedAt($media->getCreatedAt())
        ;

        return $responseMedia;
    }
}
