<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver;

use Symfony\Component\OptionsResolver\Options;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation\AbstractResolver;

/**
 * Class DefaultResolver
 *
 * @category PromotionContext
 * @package Presentation
 * @subpackage Request\DateList\Command
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class DefaultResolver extends AbstractResolver
{
    const FORMATS = [];

    /**
     * @var array $defaults List of default values for optional parameters.
     */
    protected $defaults = [
        'format' => self::FORMATS,
        'cacheStorageProvider' => '',
        'cacheDirectory' => '/tmp',
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
