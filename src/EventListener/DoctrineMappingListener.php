<?php

namespace Adeliom\EasyBlogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations in Page and Category entities,
 * because it's normally impossible to do so in a mapped superclass.
 */
class DoctrineMappingListener implements EventSubscriber
{
    public function __construct(
        /**
         * @readonly
         */
        private string $postClass,
        /**
         * @readonly
         */
        private string $categoryClass
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        $isPost     = is_a($classMetadata->getName(), $this->postClass, true);
        $isCategory = is_a($classMetadata->getName(), $this->categoryClass, true);

        if ($isPost) {
            $this->processPostMetadata($classMetadata);
        }

        if ($isCategory) {
            $this->processCategoryMetadata($classMetadata);
        }
    }

    private function processPostMetadata(ClassMetadata $classMetadata): void
    {
        if (!$classMetadata->hasAssociation('category')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'category',
                'targetEntity' => $this->categoryClass,
                'inversedBy' => 'posts',
            ]);
        }
    }

    private function processCategoryMetadata(ClassMetadata $classMetadata): void
    {
        if (!$classMetadata->hasAssociation('pages')) {
            $classMetadata->mapOneToMany([
                'fieldName' => 'posts',
                'targetEntity' => $this->postClass,
                'mappedBy' => 'category',
            ]);
        }
    }
}
