<?php

namespace Adeliom\EasyBlogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyBlogExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        foreach ($config as $key => $value) {
            if (in_array($key, ['post', 'category'])) {
                foreach ($value as $type => $class) {
                    $container->setParameter('easy_blog.'.$key.'.'.$type, $class);
                }
            } else {
                $container->setParameter('easy_blog.'.$key, $value);
            }
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'easy_blog';
    }
}
