<?php

/**
 * Front controller
 *
 * @author Abdul Awal <abdulawal.me>
 */

$base_path = __DIR__.DIRECTORY_SEPARATOR;

define("BASEPATH", $base_path);

// Include controller file
require_once BASEPATH.'Controllers/HomeController.php';

// Include core route file
include BASEPATH.'Core\Router.php';
$router = new Core\Router();

$router->get('', ['controller' => 'HomeController', 'action' => 'index']);


// Error and Exception handling
error_reporting(E_ALL);
