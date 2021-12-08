<?php

namespace Adeliom\EasyBlogBundle\Controller;

use Adeliom\EasyBlogBundle\Event\EasyBlogCategoryEvent;
use Adeliom\EasySeoBundle\Entity\SEO;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CategoryController extends AbstractController
{

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'event_dispatcher' => '?'.EventDispatcherInterface::class,
            'easy_seo.breadcrumb' => '?'.BreadcrumbCollection::class,
        ]);
    }

    public function index(Request $request, string $category = '', string $_locale = null): Response
    {
        $request->setLocale($_locale ?: $request->getLocale());

        $breadcrumb = $this->get('easy_seo.breadcrumb');
        $breadcrumb->addRouteItem('homepage', ['route' => "easy_page_index"]);
        $breadcrumb->addRouteItem('blog', ['route' => "easy_blog_category_index"]);

        if($request->attributes->get("_easy_blog_root")){
            return $this->blogRoot($request);
        }

        $template = '@EasyBlog/front/category.html.twig';

        $category = $request->attributes->get("_easy_blog_category");
        $postsQueryBuilder = $this->getDoctrine()->getRepository($this->getParameter('easy_blog.post.class'))->getByCategory($category, true);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($postsQueryBuilder)
        );

        $breadcrumb->addRouteItem($category->getName(), ['route' => "easy_blog_category_index", 'params' => ['category' => $category->getSlug()]]);

        $args = [
            'category' => $category,
            'posts'  => $pagerfanta,
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyBlogCategoryEvent($category, $args, $template);
        /**
         * @var EasyBlogCategoryEvent $result;
         */
        $result = $this->get("event_dispatcher")->dispatch($event, EasyBlogCategoryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }

    public function blogRoot(Request $request) : Response
    {
        $template = '@EasyBlog/front/root.html.twig';

        $breadcrumb = $this->get('easy_seo.breadcrumb');
        $breadcrumb->addRouteItem('homepage', ['route' => "easy_page_index"]);
        $breadcrumb->addRouteItem('blog', ['route' => "easy_blog_category_index"]);

        $categories = $this->getDoctrine()->getRepository($this->getParameter('easy_blog.category.class'))->getPublished();
        $postsQueryBuilder = $this->getDoctrine()->getRepository($this->getParameter('easy_blog.post.class'))->getPublished(true);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($postsQueryBuilder)
        );

        $args = [
            'categories' => $categories,
            'posts'  => $pagerfanta,
            'page'  => [
                'name' => null,
                'seo' => new SEO()
            ],
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyBlogCategoryEvent(null, $args, $template);
        /**
         * @var EasyBlogCategoryEvent $result;
         */
        $result = $this->get("event_dispatcher")->dispatch($event, EasyBlogCategoryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }
}
