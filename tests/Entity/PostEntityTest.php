<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Entity;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\Entity\PostEntity::class)]
final class PostEntityTest extends TestCase
{
    public function testPostMaintainsCategoryAndMetadata(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');

        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');
        $post->setCategory($category);
        $post->setCss('body { color: blue; }');
        $post->setJs('console.log("launch");');

        self::assertSame($category, $post->getCategory());
        self::assertSame('body { color: blue; }', $post->getCss());
        self::assertSame('console.log("launch");', $post->getJs());
    }

    public function testPostLifecycleUpdatesSeoAndRemovalState(): void
    {
        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');
        $post->getSEO()->title = '';

        $post->setSeoTitle(new PrePersistEventArgs($post, $this->createMock(EntityManagerInterface::class)));

        self::assertSame('Launch', $post->getSEO()->title);

        $id = new \ReflectionProperty($post, 'id');
        $id->setValue($post, 12);

        $post->onRemove(new PreRemoveEventArgs($post, $this->createMock(EntityManagerInterface::class)));

        self::assertSame(ThreeStateStatusEnum::UNPUBLISHED, $post->getState());
        self::assertSame('Launch-12-deleted', $post->getName());
        self::assertSame('launch-12-deleted', $post->getSlug());
    }
}
