<?php
namespace Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation;

use stdClass;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sfynx\ApiMediaBundle\Layers\Domain\Service\Media\Transformer\Resolver\Generalisation\Interfaces\CommandResolverInterface;

/**
 * Class AbstractResolver
 *
 * @category   Sfynx\CoreBundle\Layers
 * @package    Presentation
 * @subpackage Request\Generalisation
 * @abstract
 */
abstract class AbstractResolver implements CommandResolverInterface
{
    /** @var array */
    protected $defaults = [];
    /** @var array */
    protected $defined = [];
    /** @var array */
    protected $required = [];
    /** @var array */
    protected $allowedTypes = [];
    /** @var array */
    protected $allowedValues = [];
    /** @var array */
    protected $resolverParameters;
    /** @var array */
    protected $options;
    /** @var OptionsResolverInterface */
    protected $resolver;
    /** @var stdClass */
    protected $object;

    /**
     * AbstractResolver constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->object = new stdClass();
        $this->resolver = new OptionsResolver();

        $this->execute($options);
    }

    /**
     * @return array
     */
    protected function getNormalizers(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getResolverParameters()
    {
        return $this->resolverParameters;
    }

    /**
     *
     * @param array $options
     * @return AbstractFormResolver
     */
    protected function execute(array $options = []): AbstractResolver
    {
        $this->setOptions($options);

        $this->defaults['_token'] = null;
        $this->allowedTypes['_token'] = ['string', 'null'];

        $this->resolver->setDefaults($this->defaults);
        $this->resolver->setDefined($this->defined);
        $this->resolver->setRequired($this->required);

        foreach ($this->allowedTypes as $optionName => $optionTypes) {
            $this->resolver->setAllowedTypes($optionName, $optionTypes);
        }
        foreach ($this->allowedValues as $optionName => $optionValues) {
            $this->resolver->setAllowedValues($optionName, $optionValues);
        }
        foreach ($this->getNormalizers() as $optionName => $optionValues) {
            $this->resolver->setNormalizer($optionName, $optionValues);
        }

        $this->resolverParameters = $this->resolver->resolve($this->options);

        return $this;
    }

    /**
     * @param array $options
     * @return void
     */
    protected function setOptions(array $options = []): void
    {
        $this->options = (null !== $options) ? $options : [];
    }
}
