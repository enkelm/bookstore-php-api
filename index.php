<?php
include_once './inc/autoloader.php';

use Controllers\AdminController;
use Controllers\AuthController;
use Controllers\RolesController;
use Controllers\ProductsController;


enum Endpoints
{
    case auth;
    case admin;
    case roles;
    case products;
}

enum Methods
{
    case signup;
    case login;
    case getAll;
    case getById;
    case add;
    case put;
    case delete;
}


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

foreach (Endpoints::cases() as $controller) {
    if ((isset($uri[3]) && $uri[3] == ($controller->name)) || !isset($uri[4])) {
        foreach (Methods::cases() as $method) {
            if ($method->name === $uri[4]) {
                $objFeedController;
                switch ($controller->name) {
                    case 'admin':
                        $objFeedController = new AdminController();
                        break;
                    case 'auth':
                        $objFeedController = new AuthController();
                        break;
                    case 'roles':
                        $objFeedController = new RolesController();
                        break;
                    case 'products':
                        $objFeedController = new ProductsController();
                        break;
                    default:
                        echo 'Not implemented';
                        break;
                }
                $strMethodName = $uri[4] . 'Action';
                $objFeedController->{$strMethodName}();
                exit();
            }
        }
    }
}

header("HTTP/1.1 404 Not Found");
