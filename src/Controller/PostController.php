<?php

namespace Adeliom\EasyBlogBundle\Controller;

use Adeliom\EasyBlogBundle\Event\EasyBlogCategoryEvent;
use Adeliom\EasyBlogBundle\Event\EasyBlogPostEvent;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'event_dispatcher' => '?' . EventDispatcherInterface::class,
            'easy_seo.breadcrumb' => '?' . BreadcrumbCollection::class,
        ]);
    }

    public function index(Request $request, string $category = '', string $post = '', string $_locale = null): Response
    {
        $request->setLocale($_locale ?: $request->getLocale());
        $breadcrumb = $this->get('easy_seo.breadcrumb');
        $breadcrumb->addRouteItem('homepage', ['route' => "easy_page_index"]);
        $breadcrumb->addRouteItem('blog', ['route' => "easy_blog_category_index"]);

        $template = '@EasyBlog/front/post.html.twig';

        $category = $request->attributes->get("_easy_blog_category");
        $post = $request->attributes->get("_easy_blog_post");

        $breadcrumb->addRouteItem($category->getName(), ['route' => "easy_blog_category_index", 'params' => ['category' => $category->getSlug()]]);
        $breadcrumb->addRouteItem($post->getName(), ['route' => "easy_blog_post_index", 'params' => ['category' => $post->getCategory()->getSlug(), 'post' => $post->getSlug()]]);

        $args = [
            'category' => $category,
            'post'  => $post,
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyBlogPostEvent($post, $args, $template);
        /**
         * @var EasyBlogCategoryEvent $result;
         */
        $result = $this->get('event_dispatcher')->dispatch($event, EasyBlogCategoryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }
}
