<?php

namespace Adeliom\EasyBlogBundle\Routing;

use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class BlogCategoryLoader extends Loader
{
    private bool $isLoaded = false;

    public function __construct(
        private string $controller,
        private string $entity,
        private CategoryRepository $repository,
        private array $config,
        string $env = null
    ) {
        parent::__construct($env);
    }

    public function load($resource, string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_blog_category" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = $this->config['root_path'] . '/{category}';
        $defaults = [
            '_controller' => $this->controller . '::index',
            'category' => '',
        ];
        $requirements = [
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_blog_category') || request.attributes.get('_easy_blog_root') === true");

        // add the new route to the route collection
        $routeName = 'easy_blog_category_index';
        $routes->add($routeName, $route, -85);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'easy_blog_category' === $type;
    }
}
