<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Controller;

use Adeliom\EasyBlogBundle\Controller\PostController;
use Adeliom\EasyBlogBundle\Entity\CategoryEntity;
use Adeliom\EasyBlogBundle\Entity\PostEntity;
use Adeliom\EasyBlogBundle\Event\EasyBlogPostEvent;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(\Adeliom\EasyBlogBundle\Controller\PostController::class)]
final class PostControllerTest extends TestCase
{
    public function testSubscribedServicesExposeDispatcherAndBreadcrumb(): void
    {
        $services = PostControllerDouble::getSubscribedServices();

        self::assertSame('?'.EventDispatcherInterface::class, $services['event_dispatcher']);
        self::assertSame('?'.BreadcrumbCollection::class, $services['easy_seo.breadcrumb']);
    }

    public function testIndexRendersPostPage(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');

        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');
        $post->setCategory($category);

        $request = Request::create('/blog/news/launch');
        $request->attributes->set('_easy_blog_category', $category);
        $request->attributes->set('_easy_blog_post', $post);

        $breadcrumb = $this->createBreadcrumb();
        $dispatcher = new EventDispatcher();
        $dispatchedEvent = null;
        $dispatcher->addListener(EasyBlogPostEvent::class, static function (EasyBlogPostEvent $event) use (&$dispatchedEvent, $post): void {
            TestCase::assertSame($post, $event->getPost());
            $dispatchedEvent = $event;
        });

        $controller = new PostControllerDouble();
        $controller->setContainer($this->createContainer($breadcrumb, $dispatcher));

        $response = $controller->index($request, 'news', 'launch', 'fr');

        self::assertSame('ok', $response->getContent());
        self::assertSame('@EasyBlog/front/post.html.twig', $controller->lastTemplate);
        self::assertSame($post, $controller->lastParameters['post']);
        self::assertSame($category, $controller->lastParameters['category']);
        self::assertSame('fr', $request->getLocale());
        self::assertInstanceOf(EasyBlogPostEvent::class, $dispatchedEvent);
    }

    public function testIndexKeepsLegacyNamedListenersWorking(): void
    {
        $category = new CategoryEntity();
        $category->setName('News');
        $category->setSlug('news');

        $post = new PostEntity();
        $post->setName('Launch');
        $post->setSlug('launch');
        $post->setCategory($category);

        $request = Request::create('/blog/news/launch');
        $request->attributes->set('_easy_blog_category', $category);
        $request->attributes->set('_easy_blog_post', $post);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(EasyBlogPostEvent::NAME, static function (EasyBlogPostEvent $event): void {
            $event->setTemplate('@EasyBlog/front/post_legacy.html.twig');
        });

        $controller = new PostControllerDouble();
        $controller->setContainer($this->createContainer($this->createBreadcrumb(), $dispatcher));

        $response = $controller->index($request, 'news', 'launch', 'fr');

        self::assertSame('ok', $response->getContent());
        self::assertSame('@EasyBlog/front/post_legacy.html.twig', $controller->lastTemplate);
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

final class PostControllerDouble extends PostController
{
    public string $lastTemplate = '';

    public array $lastParameters = [];

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastTemplate = $view;
        $this->lastParameters = $parameters;

        return new Response('ok');
    }
}
