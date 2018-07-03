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
        'maxAge' => null,
        'sharedMaxAge' => null,
        'noresponse' => false,
        'signingKey' => null,
    ];

    /**
     * @var string[] $required List of required parameters for each methods.
     */
    protected $required = [
        'storage_key',
        'format',
        'cacheDirectory'
    ];

    /**
     * @param array $options
     * @return void
     */
    protected function setOptions(array $options = []): void
    {
        foreach (['maxAge', 'sharedMaxAge'] as $data) {
            if (isset($options[$data])) {
                $options[$data] = (int)$options[$data];
            }
        }

        foreach (['noresponse'] as $data) {
            if (isset($options[$data])) {
                $options[$data] = (int)$options[$data] ? true : false;
            }
        }

        $this->options = (null !== $options) ? $options : [];
    }
}
