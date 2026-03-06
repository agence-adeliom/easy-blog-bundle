<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Repository;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyBlogBundle\Repository\CategoryRepository::class)]
final class CategoryRepositoryTest extends TestCase
{
    public function testSetConfigAndGetPublishedUseConfiguredCachePolicy(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('enableResultCache')->with(600)->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['result']);

        $builder = $this->createConfiguredBuilder($query);
        $repository = new CategoryRepositoryHarness($builder);
        $repository->setConfig(['enabled' => true, 'ttl' => 600]);

        self::assertSame(['result'], $repository->getPublished());
    }

    public function testGetPublishedQueryBuildsStatusConstraint(): void
    {
        $builder = $this->createConfiguredBuilder($this->createMock(Query::class));
        $builder->expects(self::once())->method('where')->with('category.status = :status')->willReturnSelf();
        $builder->expects(self::once())->method('setParameter')->with('status', true)->willReturnSelf();

        $repository = new CategoryRepositoryHarness($builder);

        self::assertSame($builder, $repository->getPublishedQuery());
    }

    public function testGetBySlugUsesCacheFlagAndReturnsSingleResult(): void
    {
        $category = new CategoryEntity();

        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('disableResultCache')->willReturnSelf();
        $query->expects(self::once())->method('getOneOrNullResult')->willReturn($category);

        $builder = $this->createConfiguredBuilder($query);
        $builder->expects(self::once())->method('setMaxResults')->with(1)->willReturnSelf();
        $repository = new CategoryRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame($category, $repository->getBySlug('news'));
    }

    private function createConfiguredBuilder(Query $query): QueryBuilder
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'setParameter', 'getQuery', 'andWhere', 'setMaxResults', 'expr'])
            ->getMock();

        $builder->method('where')->willReturnSelf();
        $builder->method('andWhere')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query);
        $builder->method('expr')->willReturn(new Expr());

        return $builder;
    }
}

final class CategoryRepositoryHarness extends CategoryRepository
{
    public function __construct(private QueryBuilder $builder)
    {
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->builder;
    }
}
