<?php
namespace Sfynx\ApiMediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Sfynx\ApiMediaBundle\DependencyInjection\Compiler\ChangeProviderPass;
use Sfynx\ApiMediaBundle\DependencyInjection\Compiler\DefineMediaMetadataExtractorsCompilerPass;
use Sfynx\ApiMediaBundle\DependencyInjection\Compiler\DefineMediaTransformersCompilerPass;

/**
 * Media bundle
 *
 * @category   Sfynx\ApiMediaBundle
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2016-09-05
 */
class SfynxApiMediaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ChangeProviderPass());
        $container->addCompilerPass(new DefineMediaMetadataExtractorsCompilerPass());
        $container->addCompilerPass(new DefineMediaTransformersCompilerPass());
    }
}
