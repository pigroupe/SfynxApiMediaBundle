<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Command;

/**
 * Class MediaCommand.
 *
 * @category   Sfynx\ApiMediaBundle\Layers
 * @package    Domain
 * @subpackage Service\Media\Transformer\Command
 */
class MediaCommand extends DefaultCommand
{
    /** @var int */
    protected $resize;
    /** @var int */
    protected $scale;
    /** @var int */
    protected $grayscale;
    /** @var int */
    protected $rotate;
    /** @var int */
    protected $width;
    /** @var int */
    protected $height;
    /** @var int */
    protected $maxwidth;
    /** @var int */
    protected $maxheight;
    /** @var int */
    protected $minwidth;
    /** @var int */
    protected $minheight;
    /** @var bool */
    protected $noresponse;
    /** @var int */
    protected $sharedMaxAge;
    /** @var int */
    protected $maxAge;
}
