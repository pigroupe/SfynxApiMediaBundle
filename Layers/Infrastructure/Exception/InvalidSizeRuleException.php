<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */
namespace Sfynx\ApiMediaBundle\Layers\Infrastructure\Exception;

class InvalidSizeRuleException extends \Exception
{
    /**
     * The constructor.
     */
    public function __construct($unit)
    {
        parent::__construct(sprintf('The size rule %s is invalid.', $unit));
    }
}
