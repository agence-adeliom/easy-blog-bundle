<?php

namespace Adeliom\EasyBlogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Adeliom\EasyBlogBundle\DependencyInjection\EasyBlogExtension;

class EasyBlogBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EasyBlogExtension();
    }
}
