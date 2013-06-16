<?php
namespace Werkint\Bundle\WebscriptBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias)->children();

        $rootNode->scalarNode('scripts')->end();
        $rootNode->scalarNode('respath')->end();
        $rootNode->scalarNode('resdir')->end();

        $rootNode->end();
        return $treeBuilder;
    }
}