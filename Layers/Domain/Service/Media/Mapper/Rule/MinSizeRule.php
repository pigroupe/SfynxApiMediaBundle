<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Mapper\Rule;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MinSizeRule extends AbstractSizeRule
{
    /**
     * {@inheritdoc}
     */
    function check($file)
    {
        if(filesize($file) < self::convertToBytes($this->getRuleArguments())) {
            return false;
        }

        return true;
    }
}
