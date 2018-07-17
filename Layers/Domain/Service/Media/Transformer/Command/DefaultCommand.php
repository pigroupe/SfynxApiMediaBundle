<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command;

use Sfynx\CoreBundle\Layers\Application\Command\Generalisation\AbstractCommand;

/**
 * Class DefaultCommand.
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Transformer\Command
 */
class DefaultCommand extends AbstractCommand
{
    /** @var string */
    protected $format;
    /** @var string */
    protected $storage_key;
    /** @var string */
    protected $cacheStorageProvider;
    /** @var string */
    protected $cacheDirectory;
    /** @var bool */
    protected $noresponse;
    /** @var int */
    protected $sharedMaxAge;
    /** @var int */
    protected $maxAge;
    /** @var string */
    protected $signingKey;
}
