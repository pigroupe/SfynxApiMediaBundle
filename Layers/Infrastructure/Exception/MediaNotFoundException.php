<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */
namespace Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception;

class MediaNotFoundException extends \Exception
{
    /**
     * The constructor.
     */
    public function __construct($reference)
    {
        parent::__construct(sprintf('No Media found with the reference: %s.', $reference));
    }
}
