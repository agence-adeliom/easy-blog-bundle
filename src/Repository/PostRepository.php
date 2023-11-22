<?php

namespace Adeliom\EasyBlogBundle\Repository;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

class PostRepository extends ServiceEntityRepository
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    public function setConfig(array $cacheConfig)
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl = $cacheConfig['ttl'];
    }

    public function getPublishedQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('post')
            ->innerJoin('post.category', 'category')
            ->where('post.state = :state')
            ->andWhere('post.publishDate < :publishDate')
            ->andWhere('category.status = :categoryActive')
        ;

        $orModule = $qb->expr()->orx();
        $orModule->add($qb->expr()->gt('post.unpublishDate', ':unpublishDate'));
        $orModule->add($qb->expr()->isNull('post.unpublishDate'));

        $qb->andWhere($orModule);

        $qb->setParameter('categoryActive', true);
        $qb->setParameter('state', ThreeStateStatusEnum::PUBLISHED());
        $qb->setParameter('publishDate', new \DateTime());
        $qb->setParameter('unpublishDate', new \DateTime());

        return $qb;
    }

    /**
     * @return PostEntity[]
     */
    public function getPublished(bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        if ($returnQueryBuilder) {
            return $qb;
        }

        if ($this->cacheEnabled) {
            $qb = $qb->getQuery()->enableResultCache($this->cacheTtl);
        } else {
            $qb = $qb->getQuery()->disableResultCache();
        }

        return $qb->getResult();
    }

    /**
     * @return PostEntity[]
     */
    public function getByCategory(CategoryEntity $categoryEntity, bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('post.category = :category')
            ->setParameter('category', $categoryEntity)
        ;
        if ($returnQueryBuilder) {
            return $qb;
        }

        if ($this->cacheEnabled) {
            $qb = $qb->getQuery()->enableResultCache($this->cacheTtl);
        } else {
            $qb = $qb->getQuery()->disableResultCache();
        }

        return $qb->getResult();
    }

    /**
     * @return PostEntity
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySlug(string $slug, ?CategoryEntity $categoryEntity, bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('post.slug = :slug')
            ->setParameter('slug', $slug);
        if (null !== $categoryEntity) {
            $qb->andWhere('post.category = :category')
                ->setParameter('category', $categoryEntity);
        }

        $qb->setMaxResults(1);
        if ($returnQueryBuilder) {
            return $qb;
        }

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getOneOrNullResult();
    }
}
