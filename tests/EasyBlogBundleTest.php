<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests;

use Adeliom\EasyBlogBundle\DependencyInjection\EasyBlogExtension;
use Adeliom\EasyBlogBundle\EasyBlogBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\EasyBlogBundle::class)]
final class EasyBlogBundleTest extends TestCase
{
    public function testBundleReturnsExpectedContainerExtension(): void
    {
        $bundle = new EasyBlogBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(EasyBlogExtension::class, $extension);
        self::assertSame('easy_blog', $extension?->getAlias());
    }
}
