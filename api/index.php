<?php
/**
 * This file is part of the API SHOP
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
 
define("BASE_PATH", dirname(__FILE__));
 
$vendor_dir = '';
// Указываем путь к папке vendor
if (file_exists(BASE_PATH . '/../vendor')) {
    $vendor_dir = BASE_PATH . '/../vendor';
} elseif (BASE_PATH . '/../../vendor') {
    $vendor_dir = BASE_PATH . '/../../vendor';
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
 
    // Подключаем файл конфигурации системы
    require BASE_PATH . '/app/settings.php';
    $config = \ApiShop\Config\Settings::get();
 
    // Получаем список и конфигурацию пакетов
    $package = json_decode(file_get_contents($auto_require), true);
    $slimSettings = $package['require']['slim.slim']['settings'];
 
    $slim = [];
 
    $slim['debug'] = false;
    $slim['displayErrorDetails'] = false;
    $slim['addContentLengthHeader'] = false;
    $slim['determineRouteBeforeAppMiddleware'] = false;
 
/*     if (isset($slimSettings['displayErrorDetails'])) {if ((int)$slimSettings['displayErrorDetails'] == 1) {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }} */
 
    if (isset($slimSettings)) {
        foreach($slimSettings as $key => $val)
        {
            if((int)$val == 1){
                $slim[$key] = true;
            } elseif((int)$val == 0) {
                $slim[$key] = false;
            } else {
                $slim[$key] = $val;
            }
        }
    }
 
    $loader = new \Pllano\AutoRequire\Autoloader();
    $loader->register();
    $loader->addNamespace('ApiShop\\Api\\Services', __DIR__ . '/services');
 
    // Подключаем Slim и отдаем ему конфигурацию
    $app = new \Slim\App($slim);

    // Automatically register routers
    // Автоматическое подключение роутеров
    $routers = glob(__DIR__ . '/routers/*.php');
    foreach ($routers as $router) {
        require $router;
    }
 
    // Slim Run
    $app->run();
 
}
 