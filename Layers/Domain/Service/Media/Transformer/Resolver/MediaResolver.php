<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver;

use Symfony\Component\OptionsResolver\Options;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation\AbstractResolver;

/**
 * Class MediaResolver
 *
 * @category PromotionContext
 * @package Presentation
 * @subpackage Request\DateList\Command
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class MediaResolver extends AbstractResolver
{
    const FORMATS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @var array $defaults List of default values for optional parameters.
     */
    protected $defaults = [
        'format' => self::FORMATS,
        'cacheStorageProvider' => '',
        'cacheDirectory' => '/tmp',
    ];

    /**
     * @var array $defined List of default values for optional parameters.
     */
    protected $defined = [
        'resize',
        'scale',
        'grayscale',
        'rotate',
        'width',
        'height',
        'maxwidth',
        'maxheight',
        'minwidth',
        'minheight',
    ];

    /**
     * @var string[] $required List of required parameters for each methods.
     */
    protected $required = [
        'storage_key',
        'format',
        'cacheDirectory',
    ];
}
