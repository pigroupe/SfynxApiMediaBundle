<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */
namespace Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception;

class UnavailableTransformationException extends \Exception
{
    /**
     * The constructor.
     *
     * @param string $serviceName
     */
    public function __construct($options)
    {
        parent::__construct(sprintf('This transformation is unavailable: %s', json_encode($options)));
    }
}
