<?php

/**
 *
 * @author:  Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */
namespace Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception;

class ImagickException extends \Exception
{
    public function __construct($functionName)
    {
        parent::__construct(sprintf('Imagick::%s has encountered an error.', $functionName));
    }
}
