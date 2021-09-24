<?php

namespace Adeliom\EasyBlogBundle\Routing;


use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class BlogPostLoader extends Loader
{
    private $isLoaded = false;

    private $controller;
    private $entity;
    private $repository;
    private $config;


    public function __construct(string $controller, string $entity, PostRepository $repository, array $config, string $env = null)
    {
        parent::__construct($env);

        $this->controller = $controller;
        $this->config = $config;
        $this->entity = $entity;
        $this->repository = $repository;
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_blog_post" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = $this->config['root_path'] . '/{category}/{post}';
        $defaults = [
            '_controller' => $this->controller . '::index',
        ];
        $requirements = [
            'category' => "([a-zA-Z0-9_-]+\/?)*",
            'post' => "([a-zA-Z0-9_-]+\/?)*",
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_blog_category') && request.attributes.has('_easy_blog_post')");

        // add the new route to the route collection
        $routeName = 'easy_blog_post_index';
        $routes->add($routeName, $route, -80);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null)
    {
        return 'easy_blog_post' === $type;
    }
}
