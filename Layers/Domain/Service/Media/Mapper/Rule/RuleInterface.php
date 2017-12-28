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

interface RuleInterface
{
    /**
     * Check the rule for a file.
     *
     * @param string $file
     * @return boolean
     */
    public function check($file);
}
