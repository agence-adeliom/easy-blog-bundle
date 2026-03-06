<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\DependencyInjection;

use Adeliom\EasyBlogBundle\DependencyInjection\EasyBlogExtension;
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
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(\Adeliom\EasyBlogBundle\DependencyInjection\EasyBlogExtension::class)]
final class EasyBlogExtensionTest extends TestCase
{
    public function testExtensionLoadsParametersAndServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new EasyBlogExtension();

        $extension->load([[
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
                'ttl' => 1200,
            ],
            'page' => [
                'root_path' => '/articles',
            ],
            'sitemap' => false,
        ]], $container);

        self::assertSame('easy_blog', $extension->getAlias());
        self::assertSame(TestPost::class, $container->getParameter('easy_blog.post.class'));
        self::assertSame(TestPostRepository::class, $container->getParameter('easy_blog.post.repository'));
        self::assertSame(TestPostController::class, $container->getParameter('easy_blog.post.controller'));
        self::assertSame(TestPostCrudController::class, $container->getParameter('easy_blog.post.crud'));
        self::assertSame(TestCategory::class, $container->getParameter('easy_blog.category.class'));
        self::assertSame(TestCategoryRepository::class, $container->getParameter('easy_blog.category.repository'));
        self::assertSame(TestCategoryController::class, $container->getParameter('easy_blog.category.controller'));
        self::assertSame(TestCategoryCrudController::class, $container->getParameter('easy_blog.category.crud'));
        self::assertSame(['enabled' => true, 'ttl' => 1200], $container->getParameter('easy_blog.cache'));
        self::assertSame(['root_path' => '/articles'], $container->getParameter('easy_blog.page'));
        self::assertFalse($container->getParameter('easy_blog.sitemap'));
        self::assertTrue($container->hasDefinition('easy_blog.post.route_loader'));
        self::assertTrue($container->hasDefinition('easy_blog.category.route_loader'));
        self::assertTrue($container->hasDefinition('easy_blog.post.repository'));
        self::assertTrue($container->hasDefinition('easy_blog.category.repository'));
        self::assertTrue($container->hasDefinition('easy_blog.sitemap.subscriber'));
    }
}
