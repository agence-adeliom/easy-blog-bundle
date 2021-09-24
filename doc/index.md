# Setup database

## Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

## Without

```bash
php bin/console doctrine:schema:update --force
```

# Manage pages in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use App\Entity\EasyBlog\Post;
use App\Entity\EasyBlog\Category;

class DashboardController extends AbstractDashboardController
{
    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield MenuItem::section('easy.blog.blog'); // (Optional)
        yield MenuItem::linkToCrud('easy.blog.admin.menu.categories', 'fa fa-folder', Category::class);
        yield MenuItem::linkToCrud('easy.blog.admin.menu.articles', 'fa fa-file-alt', Post::class);

        ...
```
