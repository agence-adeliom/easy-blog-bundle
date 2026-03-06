<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Entity;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\Entity\CategoryEntity::class)]
final class CategoryEntityTest extends TestCase
{
    public function testCategoryMaintainsPostsAndPresentationProperties(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');
        $category->setCss('body { color: red; }');
        $category->setJs('console.log("news");');

        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');

        $category->addPost($post);

        self::assertCount(1, $category->getPosts());
        self::assertSame($category, $post->getCategory());
        self::assertSame('body { color: red; }', $category->getCss());
        self::assertSame('console.log("news");', $category->getJs());
        self::assertSame('News', (string) $category);

        $category->removePost($post);

        self::assertCount(0, $category->getPosts());
        self::assertNull($post->getCategory());
    }

    public function testCategoryLifecycleUpdatesSeoAndRemovalState(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');
        $category->getSEO()->title = '';

        $category->setSeoTitle(new PrePersistEventArgs($category, $this->createMock(EntityManagerInterface::class)));

        self::assertSame('News', $category->getSEO()->title);

        $id = new \ReflectionProperty($category, 'id');
        $id->setValue($category, 7);

        $category->onRemove(new PreRemoveEventArgs($category, $this->createMock(EntityManagerInterface::class)));

        self::assertFalse($category->getStatus());
        self::assertSame('News-7-deleted', $category->getName());
        self::assertSame('news-7-deleted', $category->getSlug());
    }
}
