<?php

namespace Adeliom\EasyBlogBundle\Event;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Symfony\Contracts\EventDispatcher\Event;

class EasyBlogCategoryEvent extends Event
{
    /**
     * @var string
     */
    public const NAME = "easyblog.category.before_render";


    public function __construct(protected ?\Adeliom\EasyBlogBundle\Entity\CategoryEntity $category, protected $args, protected $template)
    {
    }

    public function getPost(): ?CategoryEntity
    {
        return $this->category;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
