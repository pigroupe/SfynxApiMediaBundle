<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver;

use Symfony\Component\OptionsResolver\Options;

/**
 * Class MediaResolver
 *
 * @category PromotionContext
 * @package Presentation
 * @subpackage Request\DateList\Command
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class MediaResolver extends DefaultResolver
{
    const FORMATS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @var array $defaults List of default values for optional parameters.
     */
    protected $defaults = [
        'format' => self::FORMATS,
        'cacheStorageProvider' => '',
        'cacheDirectory' => '/tmp',
        'maxAge' => null,
        'sharedMaxAge' => null,
        'noresponse' => false,
        'signingKey' => null,
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
        'maxAge',
        'sharedMaxAge',
        'noresponse',
        'signingKey',
    ];

    /**
     * @var string[] $required List of required parameters for each methods.
     */
    protected $required = [
        'storage_key',
        'format',
        'cacheDirectory',
    ];

    /**
     * @param array $options
     * @return void
     */
    protected function setOptions(array $options = []): void
    {
        foreach ([
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
                     'maxAge',
                     'sharedMaxAge',
                 ] as $data) {
            if (isset($options[$data])) {
                $options[$data] = (int)$options[$data];
            }
        }

        $this->options = (null !== $options) ? $options : [];
    }
}
