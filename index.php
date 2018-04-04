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

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

session_start();

define("BASE_PATH", dirname(__FILE__));
define("APP_PATH", BASE_PATH . '');
define("CORE_PATH", APP_PATH . '/core');

// Get Simple Function
require CORE_PATH . '/function.php';
$time_start = microtime_float();
$ip = get_ip();
$escaped_url = escaped_url();

// Looking for the path to the vendor folder
if (file_exists(APP_PATH . '/vendor')) {
    define("VENDOR_PATH", BASE_PATH . '/vendor');
} /* elseif (APP_PATH . '/../vendor') {
    define("VENDOR_PATH", BASE_PATH . '/../vendor');
} */

// Specify the path to the file AutoRequire
$autoRequire = VENDOR_PATH .'/AutoRequire.php';
// Specify the path to the file auto_require.json
$auto_require = VENDOR_PATH .'/auto_require.json';
if (!file_exists($autoRequire)) {
    file_put_contents($autoRequire, file_get_contents("https://raw.githubusercontent.com/pllano/auto-require/master/AutoRequire.php"));
}
if (!file_exists($auto_require)) {
    file_put_contents($auto_require, file_get_contents("https://raw.githubusercontent.com/pllano/auto-require/master/auto_require.json"));
}

if (file_exists($autoRequire) && file_exists($auto_require)) {

    // We get the list and configuration of packages
    $package = json_decode(file_get_contents($auto_require), true);

    // Connect \Pllano\AutoRequire\Autoloader
    require $autoRequire;
    // instantiate the loader
    $require = new \Pllano\AutoRequire\Autoloader();
    // Start AutoRequire\Autoloader
    $require->run(VENDOR_PATH, $auto_require);

    // Get Config
    require CORE_PATH . '/Config.php';
    $config = \App\Core\Config::get();

    //define("SITE_ID", $config['settings']['site_id']);

    // Slim Configuration
    $routingConfig = [];
    $routingConfig['debug'] = true;
    $routingConfig['displayErrorDetails'] = true;
    $routingConfig['addContentLengthHeader'] = true;
    $routingConfig['determineRouteBeforeAppMiddleware'] = true;
    $routingConfig = routing_config($package['require']['slim.slim']['settings']);

    // Connect Slim Routing
    $routing = new \Slim\App($routingConfig);

    // Connect Routers, Containers, Middlewares
    require CORE_PATH . '/bootstrap.php';

    // Run Routing
    $routing->run();

}

$time = number_format(microtime_float() - $time_start, 3);
$memory_used = memory_used();
if ($core->get('logger') !== null && $config['admin']['logger'] == 1) {
    $core->get('logger')->info("time: {$time} - IP: {$ip} - memory_used: {$memory_used} - {$escaped_url}", []); // Run logger
}
if ($time >= 31 && $memory_used >= 70 && $config['admin']['DDoS'] == 1) {
    // Blocking by IP in .htaccess, if the request lasts longer than specified
    ban_htaccess(BASE_PATH, $ip, '24');
}
if ($routingConfig['debug'] === true || (int)$config['admin']['debug'] == 1) {
    error_reporting(E_ALL ^ E_NOTICE);
    print("time: {$time} - IP: {$ip} - memory_used: {$memory_used} - {$escaped_url}<br>");
}
 