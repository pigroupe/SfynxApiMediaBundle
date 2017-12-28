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

class CreatedAfterRule extends AbstractCreatedRule
{
    /**
     * {@inheritdoc}
     */
    function check($file)
    {
        $now = new \DateTime();
        $after = self::convertToDateTime($this->getRuleArguments());
        if($after->format('U') > $now->format('U')) {
            return false;
        }

        return true;
    }

}
