<?php 
/**
    * This file is part of the {API}$hop
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/api-shop
    * @version 1.0.1
    * @package pllano.api-shop
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/

declare(strict_types = 1);

// If one of the two databases is specified as json, run jsonDB
if ($config['db']['master'] == "json" || $config['db']['slave'] == "json") {
    (new \jsonDB\Db($config['db']['json']['dir']))->run(); // Run jsonDB\Db
}

// Set up containers
require CORE_PATH . '/containers.php';
// Automatically register containers
$_containers = glob(CORE_PATH . '/containers/*.php');
foreach ($_containers as $_container) {
    require $_container;
}

$session = $core->get('session');
$languages = $core->get('languages');
$admin_template = $core->get('admin_template');
$logger = $core->get('logger');
$template = $core->get('template');
$config = $core->get('config');

// Run User Session
// (new \Pllano\Core\Models\User())->run($config);
(new \Pllano\ApiShop\Models\User($core))->run();

// Register middleware
require CORE_PATH . '/middlewares.php';
// Automatically register middlewares
$_middlewares = glob(CORE_PATH . '/middlewares/*.php');
foreach ($_middlewares as $_middleware) {
    require $_middleware;
}

// Configuration routers
$router = $config['routers']['site'];

// Register routes
require CORE_PATH . '/routers.php';
// Automatically register routers
$_routers = glob(CORE_PATH . '/routers/*.php');
foreach ($_routers as $_router) {
    require $_router;
}
 