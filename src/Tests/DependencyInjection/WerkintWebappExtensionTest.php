<?php
namespace Werkint\Bundle\WebscriptBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Werkint\Bundle\WebscriptBundle\DependencyInjection\WerkintWebscriptExtension;

/**
 * WerkintWebscriptExtensionTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebscriptExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testRequiredConfig()
    {
        $this->loadContainer([]);
    }

    public function testConfig()
    {
        $container = $this->loadContainer([
            'scripts' => '',
            'respath' => '',
            'resdir'  => '',
        ]);

        $this->assertTrue($container->hasParameter('werkint_webscript'));
    }

    public function testServices()
    {
        $container = $this->loadContainer([
            'scripts' => '',
            'respath' => '',
            'resdir'  => '',
        ]);

        $this->assertTrue(
            $container->hasDefinition('werkint.webscript'),
            'Main service is loaded'
        );
    }

    /**
     * @param array $config
     * @return ContainerBuilder
     */
    protected function loadContainer(array $config)
    {
        $container = new ContainerBuilder();
        $loader = new WerkintWebscriptExtension();
        $loader->load([$config], $container);
        return $container;
    }

}
