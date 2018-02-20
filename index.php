<?php /**
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
 
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}
 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 
// Запускаем сессию PHP
session_start();
 
$vendor_dir = '';
// Указываем путь к папке vendor
if (file_exists(__DIR__ . '/vendor')) {
    $vendor_dir = __DIR__ . '/vendor';
} elseif (__DIR__ . '/../vendor') {
    $vendor_dir = __DIR__ . '/../vendor';
}
 
// Указываем путь к AutoRequire
$autoRequire = $vendor_dir.'/AutoRequire.php';
// Указываем путь к auto_require.json
$auto_require = $vendor_dir.'/auto_require.json';
 
if (file_exists($autoRequire) && file_exists($auto_require)) {
 
    // Connect \Pllano\AutoRequire\Autoloader
    require $autoRequire;
    // instantiate the loader
    $require = new \Pllano\AutoRequire\Autoloader();
    
    // Запускаем Автозагрузку
    $require->run($vendor_dir, $auto_require);
 
    // Получаем список и конфигурацию пакетов
    $package = json_decode(file_get_contents($auto_require), true);
 
    if ($package['require']['slim.slim']['settings']['displayErrorDetails'] == 0) {
        ini_set('error_reporting', 0);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
    }
 
	$settings = $package['require']['slim.slim']['settings'];
    // Подключаем Slim и отдаем ему конфигурацию
    $app = new \Slim\App($settings);
    // Подключаем Routers и Containers
    require __DIR__ . '/app/run.php';
    // Slim Run
    $app->run();
 
}
 
