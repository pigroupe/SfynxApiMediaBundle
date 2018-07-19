<?php
namespace Sfynx\ApiMediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SfynxApiMediaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('service/services_cmd.yml');
        $loader->load('service/services_media.yml');
        $loader->load('service/services_extractor.yml');
        $loader->load('service/services_transformer.yml');
        $loader->load('repository/media.yml');
        $loader->load('controller/controller_media_query.yml');
        $loader->load('controller/controller_media_command.yml');

        $container->setParameter('sfynx_api_media.configuration', $config);
        $container->setParameter('sfynx_api_media.cache_directory', $config['cache_directory']);
        $container->setParameter('sfynx_api_media.cache_storage_provider', $config['cache_storage_provider']);

        /*
         * Blob Storage config parameter
         */

        $container->setParameter('sfynx.apimedia.blob_storage.connection_string', $config['blob_storage']['connection_string']);

        /*
         * Mapping config parameter
         */
        if (isset($config['mapping']['provider'])) {
            $container->setParameter('sfynx.apimedia.mapping.provider', $config['mapping']['provider']);
        }

        if (isset($config['mapping']['media_class'])) {
            $container->setParameter('sfynx.apimedia.media_class', $config['mapping']['media_class']);
        }
        if (isset($config['mapping']['media_entitymanager_command'])) {
            $container->setParameter('sfynx.apimedia.media.entitymanager.command', $config['mapping']['media_entitymanager_command']);
        }
        if (isset($config['mapping']['media_entitymanager_query'])) {
            $container->setParameter('sfynx.apimedia.media.entitymanager.query', $config['mapping']['media_entitymanager_query']);
        }
        if (isset($config['mapping']['media_entitymanager'])) {
            $container->setParameter('sfynx.apimedia.media.entitymanager', $config['mapping']['media_entitymanager']);
        }

        /**
         * Signing Exclude patterns config parameter
         */
        if (isset($config['signing_excludes_pattern'])) {
            $container->setParameter('sfynx.apimedia.media.signing_excludes_pattern', $config['signing_excludes_pattern']);
        }

        /**
         * Media config parameter
         */
        if (isset($config['media'])) {
            if (isset($config['media']['quality'])) {
                $container->setParameter('sfynx.apimedia.quality', $config['media']['quality']);
            }
            if (isset($config['media']['token'])) {
                $container->setParameter('sfynx.apimedia.token', $config['media']['token']);
            }
        }

        /**
         * Extension config parameter
         */
        if (isset($config['authorized_extensions'])) {
            if (isset($config['authorized_extensions']['image'])) {
                $container->setParameter('sfynx.apimedia.authorized_extensions.image', $config['authorized_extensions']['image']);
            }
            if (isset($config['authorized_extensions']['document'])) {
                $container->setParameter('sfynx.apimedia.authorized_extensions.document', $config['authorized_extensions']['document']);
            }
        }
    }
}
