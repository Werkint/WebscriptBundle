<?php
namespace Werkint\Bundle\WebscriptBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * WerkintWebscriptExtension.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebscriptExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ) {
        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration($this->getAlias()),
            $configs
        );
        $container->setParameter(
            $this->getAlias(),
            $config
        );
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }

}
