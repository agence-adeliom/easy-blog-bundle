<?php

declare(strict_types=1);

namespace Adeliom\EasyBlogBundle\Tests\Fixtures\Controller\Admin;

use Adeliom\EasyBlogBundle\Controller\Admin\PostCrudController;

final class TestPostCrudController extends PostCrudController
{
    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }
}
