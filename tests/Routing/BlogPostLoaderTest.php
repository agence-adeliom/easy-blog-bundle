<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Routing;

use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Adeliom\EasyBlogBundle\Routing\BlogPostLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(\Adeliom\EasyBlogBundle\Routing\BlogPostLoader::class)]
final class BlogPostLoaderTest extends TestCase
{
    public function testLoaderBuildsPostRouteWithTrailingSlash(): void
    {
        $loader = new BlogPostLoader(
            'App\\Controller\\PostController',
            'App\\Entity\\Post',
            $this->createMock(PostRepository::class),
            ['root_path' => '/blog/']
        );

        self::assertTrue($loader->supports(null, 'easy_blog_post'));

        $routes = $loader->load(null, 'easy_blog_post');

        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertSame('/blog/{category}/{post}/', $routes->get('easy_blog_post_index')->getPath());
        self::assertSame('App\\Controller\\PostController::index', $routes->get('easy_blog_post_index')->getDefault('_controller'));
        self::assertSame("request.attributes.has('_easy_blog_category') && request.attributes.has('_easy_blog_post')", $routes->get('easy_blog_post_index')->getCondition());
    }

    public function testLoaderCannotBeLoadedTwice(): void
    {
        $loader = new BlogPostLoader(
            'App\\Controller\\PostController',
            'App\\Entity\\Post',
            $this->createMock(PostRepository::class),
            ['root_path' => '/blog']
        );

        $loader->load(null, 'easy_blog_post');

        $this->expectException(\RuntimeException::class);

        $loader->load(null, 'easy_blog_post');
    }
}
