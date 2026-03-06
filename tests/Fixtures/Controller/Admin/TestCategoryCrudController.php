<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Fixtures\Controller\Admin;

use Adeliom\EasyBlogBundle\Controller\Admin\CategoryCrudController;

final class TestCategoryCrudController extends CategoryCrudController
{
    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }
}
