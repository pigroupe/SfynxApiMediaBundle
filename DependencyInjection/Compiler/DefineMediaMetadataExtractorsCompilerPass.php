<?php
namespace Sfynx\ApiMediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefineMediaMetadataExtractorsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sfynx.apimedia.manager.media.entity')) {
            return;
        }
        $definition = $container->getDefinition('sfynx.apimedia.manager.media.entity');

        // MetadataExtractor
        $taggedServices = $container->findTaggedServiceIds('sfynx_api_media.metadata_extractor');
        foreach ($taggedServices as $id => $tagAttributes) {
            $definition->addMethodCall(
                'addMetadataExtractor',
                array(new Reference($id), $id)
            );
        }
    }
}
