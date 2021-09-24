<?php

namespace Adeliom\EasyBlogBundle\Controller\Admin;


use Adeliom\EasySeoBundle\Admin\Field\SEOField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

abstract class CategoryCrudController extends AbstractCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyCommon/crud/custom_panel.html.twig')
            ->addFormTheme('@EasyMedia/form/easy-media.html.twig')

            ->setPageTitle(Crud::PAGE_INDEX, "easy.blog.admin.crud.title.category." . Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, "easy.blog.admin.crud.title.category." . Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, "easy.blog.admin.crud.title.category." . Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, "easy.blog.admin.crud.title.category." . Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular("easy.blog.admin.crud.label.category.singular")
            ->setEntityLabelInPlural("easy.blog.admin.crud.label.category.plural")
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel("easy.blog.admin.crud.label.category." . $action->getName());
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
        yield from $this->metadataFields($pageName, $subject);
        yield from $this->seoFields($pageName, $subject);
        yield from $this->publishFields($pageName, $subject);
    }

    public function informationsFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.information")->addCssClass("col-8");
        yield TextField::new('name', "easy.blog.admin.field.name")
            ->setRequired(true)
            ->setColumns(12);
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
    }

    public function seoFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.seo")->collapsible()->addCssClass("col-4");
        yield SEOField::new("seo");
    }

    public function publishFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.blog.admin.panel.publication")->collapsible()->addCssClass("col-4");
        yield BooleanField::new("status", "easy.blog.admin.field.state")
            ->renderAsSwitch(true);
    }
}
