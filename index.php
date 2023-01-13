<?php
include_once './inc/autoloader.php';
// include_once './inc/cross.php';

use Controllers\RolesController;
use Controllers\UsersController;

enum Endpoints 
{
    case users;
    case roles;
}

enum Methods
{
    case getAll;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);        
$uri = explode('/', $uri);

foreach(Endpoints::cases() as $controller) {
    if ((isset($uri[3]) && $uri[3] == ($controller->name)) || !isset($uri[4])) {
        foreach(Methods::cases() as $method){
            if($method->name === $uri[4])
            {
                $objFeedController;
                switch ($controller->name) {
                    case 'users':
                        $objFeedController = new UsersController();
                        break;
                    case 'roles':
                        $objFeedController = new RolesController();
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

?>
