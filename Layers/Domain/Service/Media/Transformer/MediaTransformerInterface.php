<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Gaufrette\Filesystem;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;

interface MediaTransformerInterface
{
    /**
     * Check the format
     *
     * @param string $format
     * @return boolean
     */
    public function checkFormat($format);

    /**
     * transform
     *
     * @param Filesystem $storageProvider
     * @param Media $media
     * @return ResponseMedia
     */
    public function transform(Filesystem $storageProvider, Media $media, array $options = array());
}
