# Boost-API Framework
Boost API Framework is a fast and lightweight for restful API development

## Installation

It's not required to install via composer, just download the latest source

## Routing

Create new route in index.php

```php
// Routes
$router->get('', ['controller' => 'HomeController', 'action' => 'index']);
$router->post('api', ['controller' => 'HomeController', 'action' => 'api']);
```

## Controller

Create controller inside Controllers/ directory

```php
<?php

include BASEPATH.'Core\Controller.php';

/**
 * Home controller
 */
class HomeController extends Core\Controller
{
    /**
     * Show the index page
     *
     * @return response
     */
    public function index()
    {
        echo "Home page";
    }
}

```
