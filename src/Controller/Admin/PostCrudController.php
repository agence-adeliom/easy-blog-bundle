<?php

namespace Adeliom\EasyBlogBundle\Controller\Admin;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFieldsBundle\Admin\Field\AssociationField;
use Adeliom\EasyFieldsBundle\Admin\Field\EnumField;
use Adeliom\EasySeoBundle\Admin\Field\SEOField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

abstract class PostCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyFields/form/association_widget.html.twig')
            ->addFormTheme('@EasyCommon/crud/custom_panel.html.twig')
            ->addFormTheme('@EasyEditor/form/editor_widget.html.twig')


            ->setPageTitle(Crud::PAGE_INDEX, "easy.blog.admin.crud.title.article." . Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, "easy.blog.admin.crud.title.article." . Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, "easy.blog.admin.crud.title.article." . Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, "easy.blog.admin.crud.title.article." . Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular("easy.blog.admin.crud.label.article.singular")
            ->setEntityLabelInPlural("easy.blog.admin.crud.label.article.plural")
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel("easy.blog.admin.crud.label.article." . $action->getName());
                $actions->remove($page, $action->getAsConfigObject());
                $actions->add($page, $action->getAsConfigObject());
            }
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->get(AdminContextProvider::class)->getContext();
        $subject = $context->getEntity();

        yield IdField::new('id')->hideOnForm();
        yield from $this->informationsFields($pageName, $subject);
        yield from $this->seoFields($pageName, $subject);
        yield from $this->publishFields($pageName, $subject);
        yield from $this->metadataFields($pageName, $subject);
    }

    public function informationsFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.information")->addCssClass("col-12");
        yield TextField::new('name', "easy.blog.admin.field.name")
            ->setRequired(true)
            ->setColumns(12);
        yield AssociationField::new("category", "easy.blog.admin.field.category")
            ->listSelector(true)
            ->autocomplete()
            ->setCrudController($this->getParameter("easy_blog.category.crud"));
    }

    public function metadataFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.metadatas")->collapsible()->addCssClass("col-4");
        yield SlugField::new('slug', "easy.blog.admin.field.slug")
            ->setRequired(true)
            ->hideOnIndex()
            ->setTargetFieldName('name')
            ->setUnlockConfirmationMessage("easy.blog.admin.field.slug_edit")
            ->setColumns(12);
        ;
    }

    public function seoFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.seo")->collapsible()->addCssClass("col-4");
        yield SEOField::new("seo");
    }

    public function publishFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.publication")->collapsible()->addCssClass("col-4");
        yield EnumField::new("state", 'easy.blog.admin.field.state')
            ->setEnum(ThreeStateStatusEnum::class)
            ->setRequired(true)
            ->renderExpanded(true)
            ->renderAsBadges(true);
        yield DateTimeField::new('publishDate', "easy.blog.admin.field.publishDate")->setFormat('Y-MM-dd HH:mm')
            ->setRequired(true)
            ->hideOnIndex()
            ->setColumns(6);
        yield DateTimeField::new('unpublishDate', "easy.blog.admin.field.unpublishDate")->setFormat('Y-MM-dd HH:mm')
            ->setRequired(false)
            ->hideOnIndex()
            ->setColumns(6);
    }
}
