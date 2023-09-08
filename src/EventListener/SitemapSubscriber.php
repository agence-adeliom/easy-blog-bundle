<?php

namespace Adeliom\EasyBlogBundle\EventListener;

use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        /**
         * @readonly
         */
        private UrlGeneratorInterface $urlGenerator,
        /**
         * @readonly
         */
        private PostRepository $postRepository,
        /**
         * @readonly
         */
        private CategoryRepository $categoryRepository,
        /**
         * @readonly
         */
        private bool $sitemap = true,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {

        if ($this->sitemap) {
            $this->registerBlogCategoriesUrls($event->getUrlContainer());
            $this->registerBlogPostsUrls($event->getUrlContainer());
        }
    }

    public function registerBlogCategoriesUrls(UrlContainerInterface $urls): void
    {
        $categories = $this->categoryRepository->getPublished();
        foreach ($categories as $category) {
            if ($category->getSEO()->sitemap) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'easy_blog_category_index',
                            ['category' => $category->getSlug()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    'blog'
                );
            }
        }
    }

    public function registerBlogPostsUrls(UrlContainerInterface $urls): void
    {
        $posts = $this->postRepository->getPublished();

        foreach ($posts as $post) {
            if ($post->getSEO()->sitemap) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'easy_blog_post_index',
                            ['post' => $post->getSlug(), 'category' => $post->getCategory()?->getSlug()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    'blog'
                );
            }
        }
    }
}
