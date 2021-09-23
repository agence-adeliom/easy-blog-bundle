<?php

namespace Adeliom\EasyBlogBundle\Event;

use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Symfony\Contracts\EventDispatcher\Event;

class EasyBlogPostEvent extends Event
{

    public const NAME = "easyblog.post.before_render";

    protected $post;
    protected $args;
    protected $template;

    public function __construct(PostEntity $post, $args, $template)
    {
        $this->post = $post;
        $this->args = $args;
        $this->template = $template;
    }

    /**
     * @return PostEntity
     */
    public function getPost(): PostEntity
    {
        return $this->post;
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
