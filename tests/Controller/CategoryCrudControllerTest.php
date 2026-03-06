<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Controller;

use Adeliom\EasyBlogBundle\Controller\Admin\CategoryCrudController;
use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(\Adeliom\EasyBlogBundle\Controller\Admin\CategoryCrudController::class)]
final class CategoryCrudControllerTest extends TestCase
{
    public function testCrudConfigurationSetsThemesTitlesAndLabels(): void
    {
        $controller = new CategoryCrudControllerDouble();
        $dto = $controller->configureCrud(Crud::new())->getAsDto();

        self::assertContains('@EasyMedia/form/easy-media.html.twig', $dto->getFormThemes());
        self::assertSame('easy.blog.admin.crud.label.category.singular', (string) $dto->getEntityLabelInSingular());
        self::assertSame('easy.blog.admin.crud.label.category.plural', (string) $dto->getEntityLabelInPlural());
        self::assertSame('easy.blog.admin.crud.title.category.index', $dto->getCustomPageTitle(Crud::PAGE_INDEX)?->getMessage());
    }

    public function testFilterConfigurationAddsStatusChoiceFilter(): void
    {
        $controller = new CategoryCrudControllerDouble();
        $filters = $controller->configureFilters(Filters::new())->getAsDto();
        $filter = $filters->getFilter('state');

        self::assertNotNull($filter);
        self::assertSame(ThreeStateStatusEnum::cases(), $filter->getAsDto()->getFormTypeOption('value_type_options.choices'));
    }

    public function testActionsConfigurationRenamesBuiltInActions(): void
    {
        $controller = new CategoryCrudControllerDouble();
        $actions = Actions::new();
        foreach ([Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL] as $page) {
            foreach ([Action::INDEX, Action::EDIT, Action::DELETE, Action::NEW] as $action) {
                $actions->add($page, $action);
            }
        }

        $configured = $controller->configureActions($actions)->getAsDto(null);

        self::assertSame('easy.blog.admin.crud.label.category.new', $configured->getAction(Crud::PAGE_INDEX, Action::NEW)?->getLabel());
        self::assertSame('easy.blog.admin.crud.label.category.edit', $configured->getAction(Crud::PAGE_EDIT, Action::EDIT)?->getLabel());
    }

    public function testFieldGroupsExposeExpectedFieldDefinitions(): void
    {
        $controller = new CategoryCrudControllerDouble();
        $controller->setContainer($this->createContainer());

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_EDIT), false);

        self::assertCount(10, $fields);
        self::assertSame('name', $fields[2]->getAsDto()->getProperty());
        self::assertSame('seo', $fields[5]->getAsDto()->getProperty());
        self::assertSame('slug', $fields[7]->getAsDto()->getProperty());
        self::assertSame('status', $fields[9]->getAsDto()->getProperty());
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

final class CategoryCrudControllerDouble extends CategoryCrudController
{
    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }
}
