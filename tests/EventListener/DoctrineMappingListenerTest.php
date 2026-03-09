<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\EventListener;

use Adeliom\EasyBlogBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyBlogBundle\Tests\Fixtures\Entity\TestPost;
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
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::exactly(2))
            ->method('getName')
            ->willReturn(TestPost::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('category')
            ->willReturn(false);
        $metadata->expects(self::once())
            ->method('mapManyToOne')
            ->with([
                'fieldName' => 'category',
                'targetEntity' => TestCategory::class,
                'inversedBy' => 'posts',
            ]);

        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->expects(self::once())
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $listener->loadClassMetadata($event);
    }

    public function testListenerMapsCategoryPostsAssociation(): void
    {
        $listener = new DoctrineMappingListener(TestPost::class, TestCategory::class);
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::exactly(2))
            ->method('getName')
            ->willReturn(TestCategory::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('posts')
            ->willReturn(false);
        $metadata->expects(self::once())
            ->method('mapOneToMany')
            ->with([
                'fieldName' => 'posts',
                'targetEntity' => TestPost::class,
                'mappedBy' => 'category',
            ]);

        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->expects(self::once())
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $listener->loadClassMetadata($event);
    }
}
