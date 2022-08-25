<?php

namespace Adeliom\EasyBlogBundle\Entity;

use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityNameSlugTrait;
use Adeliom\EasyCommonBundle\Traits\EntityStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Adeliom\EasySeoBundle\Traits\EntitySeoTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('slug')]
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyBlogBundle\Repository\PostRepository::class)]
class CategoryEntity implements \Stringable
{
    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private TimestampableConstruct;
    }
    use EntityNameSlugTrait;
    use EntityStatusTrait;
    use EntitySeoTrait {
        EntitySeoTrait::__construct as private SEOConstruct;
    }

    /**
     * @var PostEntity[]|ArrayCollection
     */
    protected $posts;

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
        $this->SEOConstruct();
        $this->posts     = new ArrayCollection();
    }

    /**
     * @return PostEntity[]|ArrayCollection
     */
    public function getPosts(): array|\Doctrine\Common\Collections\ArrayCollection
    {
        return $this->posts;
    }

    public function addPost(PostEntity $post): void
    {
        $this->posts->add($post);
        if ($post->getCategory() !== $this) {
            $post->setCategory($this);
        }
    }

    public function removePost(PostEntity $post): void
    {
        $this->posts->removeElement($post);
        $post->setCategory(null);
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
        $this->setStatus(false);
        $this->setName($this->getName() . '-' . $this->getId() . '-deleted');
        $this->setSlug($this->getSlug() . '-' . $this->getId() . '-deleted');
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
