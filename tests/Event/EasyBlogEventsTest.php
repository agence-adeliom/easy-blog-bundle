<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Event;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\Event\EasyBlogCategoryEvent;
use Adeliom\EasyBlogBundle\Event\EasyBlogPostEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\Event\EasyBlogCategoryEvent::class)]
#[CoversClass(\Adeliom\EasyBlogBundle\Event\EasyBlogPostEvent::class)]
final class EasyBlogEventsTest extends TestCase
{
    public function testEventsExposeMutablePayload(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');

        $categoryEvent = new EasyBlogCategoryEvent($category, ['foo' => 'bar'], '@EasyBlog/front/category.html.twig');

        self::assertSame(EasyBlogCategoryEvent::NAME, 'easyblog.category.before_render');
        self::assertSame($category, $categoryEvent->getPost());
        self::assertSame(['foo' => 'bar'], $categoryEvent->getArgs());
        self::assertSame('@EasyBlog/front/category.html.twig', $categoryEvent->getTemplate());

        $categoryEvent->setArgs(['baz' => 'qux']);
        $categoryEvent->setTemplate('@EasyBlog/front/root.html.twig');

        self::assertSame(['baz' => 'qux'], $categoryEvent->getArgs());
        self::assertSame('@EasyBlog/front/root.html.twig', $categoryEvent->getTemplate());

        $post = new PostEntity();
        $post->setName('Launch');
        $postEvent = new EasyBlogPostEvent($post, ['post' => $post], '@EasyBlog/front/post.html.twig');

        self::assertSame(EasyBlogPostEvent::NAME, 'easyblog.post.before_render');
        self::assertSame($post, $postEvent->getPost());
        self::assertSame(['post' => $post], $postEvent->getArgs());
        self::assertSame('@EasyBlog/front/post.html.twig', $postEvent->getTemplate());

        $postEvent->setArgs(['post' => null]);
        $postEvent->setTemplate('blog/custom.html.twig');

        self::assertSame(['post' => null], $postEvent->getArgs());
        self::assertSame('blog/custom.html.twig', $postEvent->getTemplate());
    }
}
