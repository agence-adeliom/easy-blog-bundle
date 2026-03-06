<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Routing;

use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Routing\BlogCategoryLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(\Adeliom\EasyBlogBundle\Routing\BlogCategoryLoader::class)]
final class BlogCategoryLoaderTest extends TestCase
{
    public function testLoaderBuildsCategoryRoute(): void
    {
        $loader = new BlogCategoryLoader(
            'App\\Controller\\CategoryController',
            'App\\Entity\\Category',
            $this->createMock(CategoryRepository::class),
            ['root_path' => '/blog']
        );

        self::assertTrue($loader->supports(null, 'easy_blog_category'));

        $routes = $loader->load(null, 'easy_blog_category');

        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertSame('/blog/{category}', $routes->get('easy_blog_category_index')->getPath());
        self::assertSame('App\\Controller\\CategoryController::index', $routes->get('easy_blog_category_index')->getDefault('_controller'));
        self::assertSame("request.attributes.has('_easy_blog_category') || request.attributes.get('_easy_blog_root') === true", $routes->get('easy_blog_category_index')->getCondition());
    }

    public function testLoaderCannotBeLoadedTwice(): void
    {
        $loader = new BlogCategoryLoader(
            'App\\Controller\\CategoryController',
            'App\\Entity\\Category',
            $this->createMock(CategoryRepository::class),
            ['root_path' => '/blog/']
        );

        $loader->load(null, 'easy_blog_category');

        $this->expectException(\RuntimeException::class);

        $loader->load(null, 'easy_blog_category');
    }
}
