<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\EventListener;

use Adeliom\EasyBlogBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Entity\TestPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\EventListener\DoctrineMappingListener::class)]
final class DoctrineMappingListenerTest extends TestCase
{
    public function testListenerMapsPostCategoryAssociation(): void
    {
        $listener = new DoctrineMappingListener(TestPost::class, TestCategory::class);
        $metadata = new ClassMetadata(TestPost::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));

        $listener->loadClassMetadata($event);

        $mapping = $metadata->getAssociationMapping('category');

        self::assertSame('category', $mapping->fieldName);
        self::assertSame(TestCategory::class, $mapping->targetEntity);
        self::assertSame('posts', $mapping->inversedBy);
    }

    public function testListenerMapsCategoryPostsAssociation(): void
    {
        $listener = new DoctrineMappingListener(TestPost::class, TestCategory::class);
        $metadata = new ClassMetadata(TestCategory::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));

        $listener->loadClassMetadata($event);

        $mapping = $metadata->getAssociationMapping('posts');

        self::assertSame('posts', $mapping->fieldName);
        self::assertSame(TestPost::class, $mapping->targetEntity);
        self::assertSame('category', $mapping->mappedBy);
    }
}
