<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Gaufrette\FilesystemInterface;
use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;

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
    public function transform(FilesystemInterface $storageProvider, Media $media, array $options = []);
}
