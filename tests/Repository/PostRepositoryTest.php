<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Repository;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\Repository\PostRepository::class)]
final class PostRepositoryTest extends TestCase
{
    public function testGetPublishedQueryConfiguresStateAndPublicationDates(): void
    {
        $builder = $this->createConfiguredBuilder();
        $builder->expects(self::once())->method('innerJoin')->with('post.category', 'category')->willReturnSelf();
        $builder->expects(self::once())->method('where')->with('post.state = :state')->willReturnSelf();
        $builder->expects(self::exactly(3))->method('andWhere')->willReturnSelf();
        $parameters = [];
        $builder->expects(self::exactly(4))->method('setParameter')
            ->willReturnCallback(function (string $key, mixed $value) use (&$parameters, $builder): QueryBuilder {
                $parameters[] = [$key, $value];

                return $builder;
            });

        $repository = new PostRepositoryHarness($builder);

        self::assertSame($builder, $repository->getPublishedQuery());
        self::assertSame(['categoryActive', true], $parameters[0]);
        self::assertSame(['state', ThreeStateStatusEnum::PUBLISHED], $parameters[1]);
        self::assertSame('publishDate', $parameters[2][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[2][1]);
        self::assertSame('unpublishDate', $parameters[3][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[3][1]);
    }

    public function testGetPublishedReturnsBuilderOrResultsDependingOnFlagAndCache(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('enableResultCache')->with(120)->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['post']);

        $builder = $this->createConfiguredBuilder($query);
        $repository = new PostRepositoryHarness($builder);
        $repository->setConfig(['enabled' => true, 'ttl' => 120]);

        self::assertSame($builder, $repository->getPublished(true));
        self::assertSame(['post'], $repository->getPublished());
    }

    public function testGetByCategoryReturnsBuilderOrResultsDependingOnFlag(): void
    {
        $category = new CategoryEntity();
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('disableResultCache')->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['post']);

        $builder = $this->createConfiguredBuilder($query);
        $repository = new PostRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame($builder, $repository->getByCategory($category, true));
        self::assertSame(['post'], $repository->getByCategory($category));
    }

    public function testGetBySlugHandlesOptionalCategoryAndSingleResult(): void
    {
        $post = new PostEntity();
        $category = new CategoryEntity();

        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('disableResultCache')->willReturnSelf();
        $query->expects(self::once())->method('getOneOrNullResult')->willReturn($post);

        $builder = $this->createConfiguredBuilder($query);
        $builder->expects(self::once())->method('setMaxResults')->with(1)->willReturnSelf();

        $repository = new PostRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame($post, $repository->getBySlug('launch', $category));
    }

    public function testGetBySlugCanReturnQueryBuilderWhenRequested(): void
    {
        $builder = $this->createConfiguredBuilder();
        $repository = new PostRepositoryHarness($builder);

        self::assertSame($builder, $repository->getBySlug('launch', null, true));
    }

    private function createConfiguredBuilder(?Query $query = null): QueryBuilder
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['innerJoin', 'where', 'andWhere', 'setParameter', 'getQuery', 'setMaxResults', 'expr'])
            ->getMock();

        $builder->method('innerJoin')->willReturnSelf();
        $builder->method('where')->willReturnSelf();
        $builder->method('andWhere')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query ?? $this->createMock(Query::class));
        $builder->method('expr')->willReturn(new Expr());

        return $builder;
    }
}

final class PostRepositoryHarness extends PostRepository
{
    public function __construct(private QueryBuilder $builder)
    {
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->builder;
    }
}
