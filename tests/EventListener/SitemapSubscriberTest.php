<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\EventListener;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\EventListener\SitemapSubscriber;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(\Adeliom\EasyBlogBundle\EventListener\SitemapSubscriber::class)]
final class SitemapSubscriberTest extends TestCase
{
    public function testSubscriberRegistersPublishedCategoriesAndPosts(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');
        $category->getSEO()->sitemap = true;

        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');
        $post->setCategory($category);
        $post->getSEO()->sitemap = true;

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('getPublished')->willReturn([$category]);

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('getPublished')->willReturn([$post]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $generatedRoutes = [];
        $urlGenerator->expects(self::exactly(2))
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters, int $referenceType) use (&$generatedRoutes): string {
                $generatedRoutes[] = [$name, $parameters, $referenceType];

                return match ($name) {
                    'easy_blog_category_index' => 'https://example.com/blog/news',
                    'easy_blog_post_index' => 'https://example.com/blog/news/launch',
                    default => throw new \LogicException(sprintf('Unexpected route "%s".', $name)),
                };
            });

        $urls = $this->createMock(UrlContainerInterface::class);
        $addedUrls = [];
        $urls->expects(self::exactly(2))
            ->method('addUrl')
            ->willReturnCallback(static function ($url, string $section) use (&$addedUrls): void {
                $addedUrls[] = [$url->getLoc(), $section];
            });

        $event = $this->createMock(SitemapPopulateEvent::class);
        $event->method('getUrlContainer')->willReturn($urls);

        $subscriber = new SitemapSubscriber($urlGenerator, $postRepository, $categoryRepository, true);

        self::assertSame([SitemapPopulateEvent::class => 'populate'], SitemapSubscriber::getSubscribedEvents());

        $subscriber->populate($event);

        self::assertSame([
            ['easy_blog_category_index', ['category' => 'news'], UrlGeneratorInterface::ABSOLUTE_URL],
            ['easy_blog_post_index', ['post' => 'launch', 'category' => 'news'], UrlGeneratorInterface::ABSOLUTE_URL],
        ], $generatedRoutes);
        self::assertSame([
            ['https://example.com/blog/news', 'blog'],
            ['https://example.com/blog/news/launch', 'blog'],
        ], $addedUrls);
    }

    public function testSubscriberSkipsRegistrationWhenDisabled(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getPublished');

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects(self::never())->method('getPublished');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $event = $this->createMock(SitemapPopulateEvent::class);

        (new SitemapSubscriber($urlGenerator, $postRepository, $categoryRepository, false))->populate($event);

        self::assertTrue(true);
    }
}
