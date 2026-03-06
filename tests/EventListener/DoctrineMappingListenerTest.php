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
    public function testListenerMapsBlogAssociations(): void
    {
        $listener = new DoctrineMappingListener(TestPost::class, TestCategory::class);

        $postMetadata = new ClassMetadata(TestPost::class);
        $postEvent = $this->createMock(LoadClassMetadataEventArgs::class);
        $postEvent->method('getClassMetadata')->willReturn($postMetadata);

        $listener->loadClassMetadata($postEvent);

        self::assertTrue($postMetadata->hasAssociation('category'));

        $categoryMetadata = new ClassMetadata(TestCategory::class);
        $categoryEvent = $this->createMock(LoadClassMetadataEventArgs::class);
        $categoryEvent->method('getClassMetadata')->willReturn($categoryMetadata);

        $listener->loadClassMetadata($categoryEvent);

        self::assertTrue($categoryMetadata->hasAssociation('posts'));
    }
}
