<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Gaufrette\Filesystem;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;

class DefaultMediaTransformer extends AbstractMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array(null);
    }

    /**
     * {@inheritdoc}
     */
    public function process(Filesystem $storageProvider, Media $media, array $options = array())
    {
        $responseMedia = new ResponseMedia();
        $responseMedia
            ->setContent($storageProvider->read($options['storage_key']))
            ->setContentType($media->getMimeType())
            ->setLastModifiedAt($media->getCreatedAt())
        ;

        return $responseMedia;
    }
}
