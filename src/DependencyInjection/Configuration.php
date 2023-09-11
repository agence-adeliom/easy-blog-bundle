<?php

namespace Adeliom\EasyBlogBundle\DependencyInjection;

use Adeliom\EasyBlogBundle\Controller\Admin\CategoryCrudController;
use Adeliom\EasyBlogBundle\Controller\Admin\PostCrudController;
use Adeliom\EasyBlogBundle\Controller\CategoryController;
use Adeliom\EasyBlogBundle\Controller\PostController;
use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_blog');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('post')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, PostEntity::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Post class must be a valid class extending %s. "%s" given.', PostEntity::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('repository')
                            ->defaultValue(PostRepository::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, PostRepository::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Post repository must be a valid class extending %s. "%s" given.', PostRepository::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('controller')
                            ->defaultValue(PostController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, PostController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Page controller must be a valid class extending %s. "%s" given.', PostController::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('crud')
                            ->defaultValue(PostCrudController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, PostCrudController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Post crud controller must be a valid class extending %s. "%s" given.', PostCrudController::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('category')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryEntity::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Category class must be a valid class extending %s. "%s" given.', CategoryEntity::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('repository')
                            ->defaultValue(CategoryRepository::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryRepository::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Category repository must be a valid class extending %s. "%s" given.', CategoryRepository::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('controller')
                            ->defaultValue(CategoryController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Category controller must be a valid class extending %s. "%s" given.', CategoryController::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('crud')
                            ->defaultValue(CategoryCrudController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryCrudController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf('Category crud controller must be a valid class extending %s. "%s" given.', CategoryCrudController::class, $value));
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->integerNode('ttl')->defaultValue(300)->end()
                    ->end()
                ->end()
                ->arrayNode('page')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('root_path')->defaultValue('/blog')->end()
                    ->end()
                ->end()
                ->booleanNode('sitemap')
                    ->defaultValue(true)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
