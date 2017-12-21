<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer;

use Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media;
use Gaufrette\Filesystem;
use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\ResponseMedia;

class SvgMediaTransformer extends DefaultMediaTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAvailableFormats()
    {
        return array('svg');
    }
}
