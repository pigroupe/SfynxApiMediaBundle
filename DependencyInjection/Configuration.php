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
        $this->addBlobStorage($rootNode);
        $this->addStorageProviders($rootNode);
        $this->addMapping($rootNode);

        $rootNode
            ->children()
            ->scalarNode('api_public_endpoint')->isRequired()->end()
            ->scalarNode('working_directory')->isRequired()->end()
            ->scalarNode('cache_directory')->isRequired()->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    /**
     * BlobStorage config
     *
     * @param $rootNode \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     *
     * @return void
     * @access protected
     *
     * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
     */
    protected function addBlobStorage(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('blob_storage')
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('connection_string')->defaultValue('')->end()
                ->end()
            ->end()
        ->end();
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
     * Mapping config
     *
     * @param $rootNode \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     *
     * @return void
     * @access protected
     *
     * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
     */
    protected function addMapping(ArrayNodeDefinition $rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('mapping')
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('provider')->isRequired()->defaultValue('orm')->end()
                    ->scalarNode('media_class')->defaultValue('Sfynx\ApiMediaBundle\Layers\Domain\Entity\Media')->end()
                    ->scalarNode('media_entitymanager_command')->defaultValue('doctrine.orm.entity_manager')->end()
                    ->scalarNode('media_entitymanager_query')->defaultValue('doctrine.orm.entity_manager')->end()
                    ->scalarNode('media_entitymanager')->defaultValue('doctrine.orm.entity_manager')->end()
                ->end()
            ->end()
        ->end();
    }
}
