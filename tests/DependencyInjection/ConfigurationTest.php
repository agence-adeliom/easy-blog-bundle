<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\DependencyInjection;

use Adeliom\EasyBlogBundle\Controller\CategoryController;
use Adeliom\EasyBlogBundle\Controller\PostController;
use Adeliom\EasyBlogBundle\DependencyInjection\Configuration;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Controller\TestCategoryController;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Controller\TestPostController;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Controller\Admin\TestCategoryCrudController;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Controller\Admin\TestPostCrudController;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Entity\TestPost;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Repository\TestCategoryRepository;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Repository\TestPostRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(\Adeliom\EasyBlogBundle\DependencyInjection\Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testConfigurationAcceptsEntitiesAndAppliesDefaults(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'post' => ['class' => TestPost::class],
            'category' => ['class' => TestCategory::class],
        ]]);

        self::assertSame(TestPost::class, $config['post']['class']);
        self::assertSame(PostRepository::class, $config['post']['repository']);
        self::assertSame(PostController::class, $config['post']['controller']);
        self::assertSame(\Adeliom\EasyBlogBundle\Controller\Admin\PostCrudController::class, $config['post']['crud']);
        self::assertSame(TestCategory::class, $config['category']['class']);
        self::assertSame(CategoryRepository::class, $config['category']['repository']);
        self::assertSame(CategoryController::class, $config['category']['controller']);
        self::assertSame(\Adeliom\EasyBlogBundle\Controller\Admin\CategoryCrudController::class, $config['category']['crud']);
        self::assertFalse($config['cache']['enabled']);
        self::assertSame(300, $config['cache']['ttl']);
        self::assertSame('/blog', $config['page']['root_path']);
        self::assertTrue($config['sitemap']);
    }

    public function testConfigurationAcceptsCustomValues(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'post' => [
                'class' => TestPost::class,
                'repository' => TestPostRepository::class,
                'controller' => TestPostController::class,
                'crud' => TestPostCrudController::class,
            ],
            'category' => [
                'class' => TestCategory::class,
                'repository' => TestCategoryRepository::class,
                'controller' => TestCategoryController::class,
                'crud' => TestCategoryCrudController::class,
            ],
            'cache' => [
                'enabled' => true,
                'ttl' => 900,
            ],
            'page' => [
                'root_path' => '/articles',
            ],
            'sitemap' => false,
        ]]);

        self::assertSame(TestPostRepository::class, $config['post']['repository']);
        self::assertSame(TestPostController::class, $config['post']['controller']);
        self::assertSame(TestPostCrudController::class, $config['post']['crud']);
        self::assertSame(TestCategoryRepository::class, $config['category']['repository']);
        self::assertSame(TestCategoryController::class, $config['category']['controller']);
        self::assertSame(TestCategoryCrudController::class, $config['category']['crud']);
        self::assertTrue($config['cache']['enabled']);
        self::assertSame(900, $config['cache']['ttl']);
        self::assertSame('/articles', $config['page']['root_path']);
        self::assertFalse($config['sitemap']);
    }

    public function testConfigurationRejectsInvalidPostClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'post' => ['class' => \stdClass::class],
            'category' => ['class' => TestCategory::class],
        ]]);
    }

    public function testConfigurationRejectsInvalidCategoryClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'post' => ['class' => TestPost::class],
            'category' => ['class' => \stdClass::class],
        ]]);
    }
}
