<?php
namespace Sfynx\ApiMediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sfynx_api_media');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        \Sfynx\CoreBundle\DependencyInjection\Configuration::addMappingConfig($rootNode);
        $this->addStorageProviders($rootNode);
        $this->addExcludeSigningPattern($rootNode);
        $this->addMediaConfig($rootNode);
        $this->addExtensionConfig($rootNode);

        $rootNode
            ->children()
                ->scalarNode('api_public_endpoint')->isRequired()->end()
                ->scalarNode('working_directory')->isRequired()->end()
                ->scalarNode('cache_directory')->isRequired()->end()
                ->scalarNode('cache_storage_provider')->defaultValue('')->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    /**
     * Mapping config
     *
     * @param $rootNode \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     *
     * @return void
     * @access protected
     *
     * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
     */
    protected function addStorageProviders(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('storage_providers')
            ->isRequired()
                ->prototype('array')
                ->children()
                    ->arrayNode('mime_types')->prototype('scalar')->end()->defaultValue([])->end()
                    ->scalarNode('max_size')->defaultNull()->end()
                    ->scalarNode('min_size')->defaultNull()->end()
                    ->scalarNode('created_before')->defaultNull()->end()
                    ->scalarNode('created_after')->defaultNull()->end()
                ->end()
            ->end()
        ->end()
        ;
    }

    /**
     * Exclude Signing Pattern config
     *
     * @param $rootNode \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     *
     * @return void
     * @access protected
     *
     * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
     */
    protected function addExcludeSigningPattern(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('signing_excludes_pattern')
                ->prototype('array')
                    ->children()
                        ->scalarNode('resize')->end()
                        ->scalarNode('scale')->end()
                        ->scalarNode('grayscale')->end()
                        ->scalarNode('rotate')->end()
                        ->scalarNode('width')->end()
                        ->scalarNode('height')->end()
                        ->scalarNode('maxwidth')->end()
                        ->scalarNode('maxheight')->end()
                        ->scalarNode('minwidth')->end()
                        ->scalarNode('minheight')->end()
//                        ->arrayNode('width')
//                            ->prototype('scalar')->end()
//                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
    }

    /**
     * Media config
     *
     * @param $rootNode ArrayNodeDefinition Class
     *
     * @return void
     * @access protected
     * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
     */
    protected function addMediaConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('media')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('quality')->defaultValue(95)->end()
                    ->arrayNode('token')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('start')->defaultValue(0)->end()
                            ->integerNode('expire')->defaultValue(3600)->end()
                            ->booleanNode('unique')->defaultValue(false)->end()
                            ->arrayNode('ipRange')->prototype('scalar')->end()->defaultValue([])->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * Extension config
     *
     * @param $rootNode ArrayNodeDefinition Class
     *
     * @return void
     * @access protected
     * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
     */
    protected function addExtensionConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('authorized_extensions')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('image')->prototype('scalar')->end()->defaultValue(['jpeg', 'jpg', 'png', 'gif'])->end()
                    ->arrayNode('document')->prototype('scalar')->end()->defaultValue(['json', 'xml', 'csv', 'pdf', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'xlsm', 'odt', 'txt'])->end()
                ->end()
            ->end()
        ->end();
    }
}
