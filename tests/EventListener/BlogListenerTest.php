<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\EventListener;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\EventListener\BlogListener;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(\Adeliom\EasyBlogBundle\EventListener\BlogListener::class)]
final class BlogListenerTest extends TestCase
{
    public function testSubscribedEventsExposeRequestListener(): void
    {
        self::assertSame([
            KernelEvents::REQUEST => ['setRequestLayout', 33],
        ], BlogListener::getSubscribedEvents());
    }

    public function testListenerMarksRootPathRequest(): void
    {
        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects(self::never())->method('getBySlug');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getBySlug');

        $listener = new BlogListener($postRepository, $categoryRepository, ['root_path' => '/blog']);
        $request = Request::create('/blog');

        $listener->setRequestLayout($this->createRequestEvent($request));

        self::assertTrue($request->attributes->getBoolean('_easy_blog_root'));
    }

    public function testListenerLoadsCategoryAndPostFromRequestPath(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');

        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');
        $post->setCategory($category);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())
            ->method('getBySlug')
            ->with('news')
            ->willReturn($category);

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects(self::once())
            ->method('getBySlug')
            ->with('launch', $category)
            ->willReturn($post);

        $listener = new BlogListener($postRepository, $categoryRepository, ['root_path' => '/blog']);
        $request = Request::create('/blog/news/launch');

        $listener->setRequestLayout($this->createRequestEvent($request));

        self::assertSame($category, $request->attributes->get('_easy_blog_category'));
        self::assertSame($post, $request->attributes->get('_easy_blog_post'));
    }

    public function testListenerIgnoresPathsOutsideConfiguredRoot(): void
    {
        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects(self::never())->method('getBySlug');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getBySlug');

        $listener = new BlogListener($postRepository, $categoryRepository, ['root_path' => '/blog']);
        $request = Request::create('/contact');

        $listener->setRequestLayout($this->createRequestEvent($request));

        self::assertFalse($request->attributes->has('_easy_blog_root'));
        self::assertFalse($request->attributes->has('_easy_blog_category'));
        self::assertFalse($request->attributes->has('_easy_blog_post'));
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
