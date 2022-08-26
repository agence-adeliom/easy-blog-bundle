
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-blog-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-blog-bundle)

# Easy Blog Bundle

Provide a basic blogging system for Easyadmin.


## Features

- A Easyadmin CRUD interface to manage blog elements

## Installation with Symfony Flex

Add our recipes endpoint

```json
{
  "extra": {
    "symfony": {
      "endpoint": [
        "https://api.github.com/repos/agence-adeliom/symfony-recipes/contents/index.json?ref=flex/main",
        ...
        "flex://defaults"
      ],
      "allow-contrib": true
    }
  }
}
```

Install with composer

```bash
composer require agence-adeliom/easy-blog-bundle
```

### Setup database

#### Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

#### Without

```bash
php bin/console doctrine:schema:update --force
```


## Documentation

### Manage in your Easyadmin dashboard

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

### Customize blog's root path

```yaml
#config/packages/easy_blog.yaml
easy_blog:
  ...
  page:
    root_path: '/blog'
```

NOTE : You will need to clear your cache after change because the RouteLoader need to be cleared.

## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@arnaud-ritti](https://github.com/arnaud-ritti)

  
