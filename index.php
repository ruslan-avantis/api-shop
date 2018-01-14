<?php
// {API}$hop
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
 
// Вывод ошибок. Что бы выключить закоментируйте эти строки
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    
    if (is_file($file)) {
        return false;
    }
}
 
// Connect \AutoRequire\Autoloader
require __DIR__ . '/vendor/AutoRequire.php';
 
// instantiate the loader
$require = new \AutoRequire\Autoloader;
 
// Указываем путь к папке vendor для AutoRequire
$vendor_dir = __DIR__ . '/vendor';
 
// Указываем путь к auto_require.json
$auto_require_min = __DIR__ . '/vendor/auto_require_min.json';
$auto_require = __DIR__ . '/vendor/auto_require.json';
 
if (file_exists(__DIR__ . '/../vendor/_autoload.php')) {
 
    // Запускаем Автозагрузку
    $require->run($vendor_dir, $auto_require_min);
 
    // Подключаем Composer
    require __DIR__ . '/../vendor/autoload.php';
 
} else {
 
    // Запускаем Автозагрузку
    $require->run($vendor_dir, $auto_require);
 
}
 
// Подключаем файл конфигурации системы
require __DIR__ . '/app/config/settings.php';
// Получаем конфигурацию
$settings = new \ApiShop\Config\Settings();
$config = $settings->get();
 
// Подключаем Slim и отдаем ему конфигурацию
$app = new \Slim\App($config);
 
// Подключаем Slim Container
require __DIR__ . '/app/config/container.php';
 
// Запускаем сессию PHP
session_start();
// Run User Session
// Запускаем сессию пользователя
(new \ApiShop\Resources\User())->run();
 
// Automatically register routers
// Автоматическое подключение роутеров
$routers = glob(__DIR__ . '/app/routers/*.php');
foreach ($routers as $router) {
    require $router;
}
 
// Если одина из баз json запускаем jsonDB
if ($config["db"]["master"] == "json" || $config["db"]["slave"] == "json") {
    // Запускаем jsonDB\Db
    $jsonDb = new \jsonDB\Db($config['db']['json']['dir']);
    $jsonDb->setCached($config['db']['json']['cached']);
    $jsonDb->setCacheLifetime($config['db']['json']['cache_lifetime']);
    $jsonDb->setTemp($config['db']['json']['temp']);
    $jsonDb->setApi($config['db']['json']['api']);
    $jsonDb->setCrypt($config['db']['json']['crypt']);
    $jsonDb->setKey($config["db"]["key"]);
    $jsonDb->run();
}
 
// Slim Run
$app->run();
 