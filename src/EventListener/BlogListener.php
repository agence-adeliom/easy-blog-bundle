<?php

namespace Adeliom\EasyBlogBundle\EventListener;

use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BlogListener implements EventSubscriberInterface
{

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $config;

    public function __construct(PostRepository $postRepository, CategoryRepository $categoryRepository, $config)
    {
        $this->postRepository    = $postRepository;
        $this->categoryRepository    = $categoryRepository;
        $this->config    = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['setRequestLayout', 33],
        ];
    }

    public function setRequestLayout(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Get the necessary informations to check them in layout configurations
        $path = $request->getPathInfo();
        $host = $request->getHost();

        if(strpos($path, $this->config["root_path"]) === false){
            return;
        }

        $prefixes = preg_split('~/~', $this->config["root_path"], -1, PREG_SPLIT_NO_EMPTY);
        /** @var PostEntity[] $pages */
        $slugsArray = preg_split('~/~', $path, -1, PREG_SPLIT_NO_EMPTY);

        if($this->config["root_path"] != "/"){
            $slugsArray = array_values(array_diff($slugsArray, $prefixes));
        }

        if(!empty($slugsArray)) {
            $category = $this->categoryRepository->getBySlug($slugsArray[0]);
            if ($category instanceof CategoryEntity) {
                $event->getRequest()->attributes->set('_easy_blog_category', $category);

                if (isset($slugsArray[1])) {
                    $post = $this->postRepository->getBySlug($slugsArray[1], $category);
                    if ($post instanceof PostEntity) {
                        $event->getRequest()->attributes->set('_easy_blog_post', $post);
                    }
                }
            }
        }else{
            if(!empty($prefixes) && (count($slugsArray) === 0)){
                $event->getRequest()->attributes->set('_easy_blog_root', true);
            }
        }


    }
}
