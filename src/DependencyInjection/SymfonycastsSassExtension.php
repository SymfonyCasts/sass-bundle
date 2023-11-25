<?php

/*
 * This file is part of the SymfonyCasts SassBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\SassBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class SymfonycastsSassExtension extends Extension implements ConfigurationInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['embed_sourcemap'])) {
            $config['sass_options']['embed_source_map'] = $config['embed_sourcemap'];
        }

        $container->findDefinition('sass.builder')
            ->replaceArgument(0, $config['root_sass'])
            ->replaceArgument(1, '%kernel.project_dir%/var/sass')
            ->replaceArgument(3, $config['binary'])
            ->replaceArgument(4, $config['sass_options'])
        ;

        $container->findDefinition('sass.css_asset_compiler')
            ->replaceArgument(0, $config['root_sass'])
            ->replaceArgument(1, '%kernel.project_dir%/var/sass')
        ;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return $this;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfonycasts_sass');

        $rootNode = $treeBuilder->getRootNode();
        \assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->arrayNode('root_sass')
                    ->info('Path to your Sass root file')
                    ->cannotBeEmpty()
                    ->scalarPrototype()
                        ->end()
                    ->validate()
                        ->ifTrue(static function (array $paths): bool {
                            if (1 === \count($paths)) {
                                return false;
                            }

                            $filenames = [];
                            foreach ($paths as $path) {
                                $filename = basename($path, '.scss');
                                $filenames[$filename] = $filename;
                            }

                            return \count($filenames) !== \count($paths);
                        })
                        ->thenInvalid('The "root_sass" paths need to end with unique filenames.')
                        ->end()
                    ->defaultValue(['%kernel.project_dir%/assets/styles/app.scss'])
                ->end()
                ->scalarNode('binary')
                    ->info('The Sass binary to use')
                    ->defaultNull()
                ->end()
                ->arrayNode('sass_options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('charset')
                            ->info('Whether to include the charset declaration in the generated Sass.')
                            ->defaultValue(true)
                        ->end()
                        ->enumNode('style')
                            ->info('The style of the generated CSS: compressed or expanded.')
                            ->values(['compressed', 'expanded'])
                            ->defaultValue('expanded')
                        ->end()
                        ->booleanNode('source_map')
                            ->info('Generate a source map file.')
                            ->defaultValue(true)
                        ->end()
                        ->booleanNode('embed_source_map')
                            ->info('Embed the sourcemap in the compiled CSS. By default, enabled only when debug mode is on.')
                            ->defaultValue('%kernel.debug%')
                        ->end()
                        ->booleanNode('embed_sources')
                            ->info('Embed the content of the Sass sources in the generated source map.')
                            ->defaultValue(false)
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('embed_sourcemap')
                    ->setDeprecated('symfonycast/sass-bundle', '0.3', 'Option "%node%" at "%path%" is deprecated. Use "sass_options.embed_source_map" instead".')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
