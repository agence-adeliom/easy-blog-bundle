<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Controller;

use Adeliom\EasyBlogBundle\Controller\CategoryController;
use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Repository\CategoryRepository;
use Adeliom\EasyBlogBundle\Repository\PostRepository;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(\Adeliom\EasyBlogBundle\Controller\CategoryController::class)]
final class CategoryControllerTest extends TestCase
{
    public function testSubscribedServicesExposeDispatcherAndBreadcrumb(): void
    {
        $services = CategoryControllerDouble::getSubscribedServices();

        self::assertSame('?'.EventDispatcherInterface::class, $services['event_dispatcher']);
        self::assertSame('?'.BreadcrumbCollection::class, $services['easy_seo.breadcrumb']);
    }

    public function testIndexRendersBlogRootWhenRootAttributeIsSet(): void
    {
        $request = Request::create('/blog');
        $request->attributes->set('_easy_blog_root', true);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())->method('getPublished')->willReturn([]);

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects(self::once())->method('getPublished')->with(true)->willReturn($this->createMock(QueryBuilder::class));

        $controller = new CategoryControllerDouble($this->createManagerRegistry($categoryRepository, $postRepository));
        $controller->setParameters([
            'easy_blog.category.class' => CategoryEntity::class,
            'easy_blog.post.class' => 'PostEntity',
        ]);
        $controller->setContainer($this->createContainer(
            $this->createBreadcrumb(),
            $this->createEventDispatcher()
        ));

        $response = $controller->index($request, '', 'fr');

        self::assertSame('ok', $response->getContent());
        self::assertSame('@EasyBlog/front/root.html.twig', $controller->lastTemplate);
        self::assertSame('fr', $request->getLocale());
        self::assertArrayHasKey('categories', $controller->lastParameters);
        self::assertArrayHasKey('posts', $controller->lastParameters);
        self::assertArrayHasKey('page', $controller->lastParameters);
    }

    public function testIndexRendersCategoryPageWithResolvedRepositories(): void
    {
        $request = Request::create('/blog/news');
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');
        $request->attributes->set('_easy_blog_category', $category);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getPublished');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects(self::once())
            ->method('getByCategory')
            ->with($category, true)
            ->willReturn($queryBuilder);

        $controller = new CategoryControllerDouble($this->createManagerRegistry($categoryRepository, $postRepository));
        $controller->setParameters([
            'easy_blog.category.class' => CategoryEntity::class,
            'easy_blog.post.class' => 'PostEntity',
        ]);
        $controller->setContainer($this->createContainer(
            $this->createBreadcrumb(),
            $this->createEventDispatcher()
        ));

        $response = $controller->index($request, 'news', 'fr');

        self::assertSame('ok', $response->getContent());
        self::assertSame('@EasyBlog/front/category.html.twig', $controller->lastTemplate);
        self::assertSame($category, $controller->lastParameters['category']);
        self::assertArrayHasKey('posts', $controller->lastParameters);
        self::assertSame('fr', $request->getLocale());
    }

    private function createManagerRegistry(CategoryRepository $categoryRepository, PostRepository $postRepository): ManagerRegistry
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('getRepository')
            ->willReturnMap([
                [CategoryEntity::class, null, $categoryRepository],
                ['PostEntity', null, $postRepository],
            ]);

        return $registry;
    }

    private function createBreadcrumb(): BreadcrumbCollection
    {
        $generator = new class implements \Symfony\Component\Routing\Generator\UrlGeneratorInterface {
            private \Symfony\Component\Routing\RequestContext $context;

            public function __construct()
            {
                $this->context = new \Symfony\Component\Routing\RequestContext();
            }

            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return '/'.$name.([] === $parameters ? '' : '?'.http_build_query($parameters));
            }

            public function setContext(\Symfony\Component\Routing\RequestContext $context): void
            {
                $this->context = $context;
            }

            public function getContext(): \Symfony\Component\Routing\RequestContext
            {
                return $this->context;
            }
        };

        $breadcrumb = new BreadcrumbCollection();
        $breadcrumb->setGenerator($generator);

        return $breadcrumb;
    }

    private function createEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        return $dispatcher;
    }

    private function createContainer(BreadcrumbCollection $breadcrumb, EventDispatcherInterface $dispatcher): ContainerInterface
    {
        return new class($breadcrumb, $dispatcher) implements ContainerInterface {
            public function __construct(private BreadcrumbCollection $breadcrumb, private EventDispatcherInterface $dispatcher)
            {
            }

            public function get(string $id)
            {
                return match ($id) {
                    'easy_seo.breadcrumb' => $this->breadcrumb,
                    'event_dispatcher' => $this->dispatcher,
                    default => throw new \InvalidArgumentException('Unknown service '.$id),
                };
            }

            public function has(string $id): bool
            {
                return \in_array($id, ['easy_seo.breadcrumb', 'event_dispatcher'], true);
            }
        };
    }
}

final class CategoryControllerDouble extends CategoryController
{
    public string $lastTemplate = '';

    public array $lastParameters = [];

    private array $parameters = [];

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    protected function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        return $this->parameters[$name];
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastTemplate = $view;
        $this->lastParameters = $parameters;

        return new Response('ok');
    }
}
