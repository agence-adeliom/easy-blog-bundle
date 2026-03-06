<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Controller;

use Adeliom\EasyBlogBundle\Controller\Admin\PostCrudController;
use Adeliom\EasyFieldsBundle\Admin\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(\Adeliom\EasyBlogBundle\Controller\Admin\PostCrudController::class)]
final class PostCrudControllerTest extends TestCase
{
    public function testCrudConfigurationSetsThemesTitlesAndLabels(): void
    {
        $controller = new PostCrudControllerDouble();
        $dto = $controller->configureCrud(Crud::new())->getAsDto();

        self::assertContains('@EasyFields/form/association_widget.html.twig', $dto->getFormThemes());
        self::assertSame('easy.blog.admin.crud.label.article.singular', (string) $dto->getEntityLabelInSingular());
        self::assertSame('easy.blog.admin.crud.title.article.index', $dto->getCustomPageTitle(Crud::PAGE_INDEX)?->getMessage());
    }

    public function testFilterConfigurationAddsStatusChoiceFilter(): void
    {
        $controller = new PostCrudControllerDouble();
        $filters = $controller->configureFilters(Filters::new())->getAsDto();

        self::assertNotNull($filters->getFilter('state'));
    }

    public function testActionsConfigurationRenamesBuiltInActions(): void
    {
        $controller = new PostCrudControllerDouble();
        $actions = Actions::new();
        foreach ([Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL] as $page) {
            foreach ([Action::INDEX, Action::EDIT, Action::DELETE, Action::NEW] as $action) {
                $actions->add($page, $action);
            }
        }

        $configured = $controller->configureActions($actions)->getAsDto(null);

        self::assertSame('easy.blog.admin.crud.label.article.new', $configured->getAction(Crud::PAGE_INDEX, Action::NEW)?->getLabel());
        self::assertSame('easy.blog.admin.crud.label.article.edit', $configured->getAction(Crud::PAGE_EDIT, Action::EDIT)?->getLabel());
    }

    public function testFieldGroupsExposeExpectedFieldDefinitions(): void
    {
        $controller = new PostCrudControllerDouble();
        $controller->setParameters([
            'easy_blog.category.crud' => 'App\\Controller\\Admin\\CategoryCrudController',
        ]);
        $controller->setContainer($this->createContainer());

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_EDIT), false);

        self::assertCount(13, $fields);
        self::assertSame('name', $fields[2]->getAsDto()->getProperty());
        self::assertSame('seo', $fields[5]->getAsDto()->getProperty());
        self::assertSame('category', $fields[8]->getAsDto()->getProperty());
        self::assertInstanceOf(AssociationField::class, $fields[8]);
        self::assertSame('state', $fields[10]->getAsDto()->getProperty());
        self::assertInstanceOf(ChoiceField::class, $fields[10]);
        self::assertSame('publishDate', $fields[11]->getAsDto()->getProperty());
    }

    private function createContainer(): ContainerInterface
    {
        $provider = new AdminContextProvider(new \Symfony\Component\HttpFoundation\RequestStack());

        return new class($provider) implements ContainerInterface {
            public function __construct(private AdminContextProvider $provider)
            {
            }

            public function get(string $id)
            {
                if (AdminContextProvider::class === $id) {
                    return $this->provider;
                }

                throw new \InvalidArgumentException('Unknown service '.$id);
            }

            public function has(string $id): bool
            {
                return AdminContextProvider::class === $id;
            }
        };
    }
}

final class PostCrudControllerDouble extends PostCrudController
{
    private array $parameters = [];

    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    protected function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        return $this->parameters[$name];
    }
}
