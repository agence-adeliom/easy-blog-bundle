<?php

namespace Adeliom\EasyBlogBundle\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityNameSlugTrait;
use Adeliom\EasyCommonBundle\Traits\EntityPublishableTrait;
use Adeliom\EasyCommonBundle\Traits\EntityThreeStateStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Adeliom\EasySeoBundle\Traits\EntitySeoTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("slug")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\MappedSuperclass(repositoryClass="Adeliom\EasyBlogBundle\Repository\PostRepository")
 */
class PostEntity {

    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private __TimestampableConstruct;
    }

    use EntityNameSlugTrait;
    use EntityThreeStateStatusTrait;
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private __PublishableConstruct;
    }

    use EntitySeoTrait {
        EntitySeoTrait::__construct as private __SEOConstruct;
    }

    /**
     * @var null|CategoryEntity
     * @Assert\Type(CategoryEntity::class)
     */
    protected $category;

    /**
     * @var string|null
     *
     * @ORM\Column(name="css", type="text", nullable=true)
     * @Assert\Type("string")
     */
    protected $css;

    /**
     * @var string|null
     *
     * @ORM\Column(name="js", type="text", nullable=true)
     * @Assert\Type("string")
     */
    protected $js;

    public function __construct()
    {
        $this->__TimestampableConstruct();
        $this->__PublishableConstruct();
        $this->__SEOConstruct();
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string|null
     */
    public function getCss(): ?string
    {
        return $this->css;
    }

    /**
     * @param string $css
     */
    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    /**
     * @return string|null
     */
    public function getJs(): ?string
    {
        return $this->js;
    }

    /**
     * @param string $js
     */
    public function setJs(string $js): void
    {
        $this->js = $js;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setSeoTitle(LifecycleEventArgs $event): void
    {
        if(empty($this->getSEO()->title)){
            $this->getSEO()->title = $this->getName();
        }
    }


    /**
     * @ORM\PreRemove()
     */
    public function onRemove(LifecycleEventArgs $event): void
    {
        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
        $this->setName($this->getName() . '-'.$this->getId().'-deleted');
        $this->setSlug($this->getSlug() . '-'.$this->getId().'-deleted');
    }
}
