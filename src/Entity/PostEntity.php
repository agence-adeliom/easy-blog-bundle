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

#[UniqueEntity('slug')]
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyBlogBundle\Repository\PostRepository::class)]
class PostEntity
{
    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private TimestampableConstruct;
    }
    use EntityNameSlugTrait;
    use EntityThreeStateStatusTrait;
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private PublishableConstruct;
    }
    use EntitySeoTrait {
        EntitySeoTrait::__construct as private SEOConstruct;
    }

    /**
     * @var null|CategoryEntity
     */
    #[Assert\Type(CategoryEntity::class)]
    protected $category;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'css', type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $css = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'js', type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $js = null;

    public function __construct()
    {
        $this->TimestampableConstruct();
        $this->PublishableConstruct();
        $this->SEOConstruct();
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    public function getJs(): ?string
    {
        return $this->js;
    }

    public function setJs(string $js): void
    {
        $this->js = $js;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSeoTitle(LifecycleEventArgs $event): void
    {
        if (empty($this->getSEO()->title)) {
            $this->getSEO()->title = $this->getName();
        }
    }

    #[ORM\PreRemove]
    public function onRemove(LifecycleEventArgs $event): void
    {
        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
        $this->setName($this->getName() . '-' . $this->getId() . '-deleted');
        $this->setSlug($this->getSlug() . '-' . $this->getId() . '-deleted');
    }
}
