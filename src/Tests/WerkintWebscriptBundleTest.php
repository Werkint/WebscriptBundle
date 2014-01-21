<?php
namespace Werkint\Bundle\WebscriptBundle\Tests\Currency;

use Werkint\Bundle\WebscriptBundle\WerkintWebscriptBundle;

/**
 * WerkintWebscriptBundleTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebscriptBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testPasses()
    {
        $containerBuilderMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $obj = new WerkintWebscriptBundle();
        $obj->build($containerBuilderMock);
    }
}