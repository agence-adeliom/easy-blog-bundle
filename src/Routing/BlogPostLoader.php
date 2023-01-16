<?php

namespace Adeliom\EasyBlogBundle\Routing;

use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class BlogPostLoader extends Loader
{
    private bool $isLoaded = false;

    public function __construct(
        private string $controller,
        private string $entity,
        private PostRepository $repository,
        private array $config,
        string $env = null
    ) {
        parent::__construct($env);
    }

    public function load($resource, string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_blog_post" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $hasTrailingSlash = str_ends_with($this->config['root_path'], '/');
        $path = $this->config['root_path'].($hasTrailingSlash?'':'/').'{category}/{post}'.($hasTrailingSlash?'/':'');
        $defaults = [
            '_controller' => $this->controller.'::index',
        ];
        $requirements = [
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_blog_category') && request.attributes.has('_easy_blog_post')");

        // add the new route to the route collection
        $routeName = 'easy_blog_post_index';
        $routes->add($routeName, $route, -80);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'easy_blog_post' === $type;
    }
}
